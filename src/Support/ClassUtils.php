<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

use Composer\Autoload\ClassLoader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Foundation\Application;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use RuntimeException;
use StickleApp\Core\Traits\StickleEntity;
use Throwable;

class ClassUtils
{
    /**
     * The shape a model is recorded as in a model_class column: the class
     * basename, so `App\Models\User` is stored as `User`.
     *
     * Deliberately not getMorphClass(). No morph map is registered anywhere in
     * the package, so getMorphClass() would return a fully-qualified name and
     * disagree with every row already written. Use this on both sides of a
     * model_class comparison and the two cannot drift.
     *
     * @param  Model|class-string  $model
     */
    public static function storeModelClass(mixed $model): string
    {
        return class_basename(is_object($model) ? $model::class : $model);
    }

    /**
     * Resolve a stored model_class back to a loadable class name.
     *
     * The inverse of storeModelClass(), and the reason that method cannot just
     * store the fully-qualified name: the round trip assumes every tracked
     * model lives under the single configured namespace. That assumption is
     * the package's, not this method's -- see stickle.namespaces.models.
     *
     * @return class-string
     */
    public static function resolveModelClass(string $stored): string
    {
        $resolved = self::tryResolveModelClass($stored);

        throw_unless(
            $resolved !== null,
            RuntimeException::class,
            sprintf(
                'Model [%s] resolved to [%s], which does not exist.',
                $stored,
                config('stickle.namespaces.models').'\\'.ucfirst($stored)
            )
        );

        return $resolved;
    }

    /**
     * A route-parameter pattern matching only the models that use
     * StickleEntity, so /stickle/{modelClass} 404s on an unknown name instead
     * of reaching a view that tries to load it.
     *
     * Every failure mode returns the unconstrained pattern rather than
     * raising. This runs while routes are registered -- including from
     * composer scripts that boot before config/stickle.php exists, where the
     * namespace is null -- and a pattern that cannot be built is not a reason
     * for the application to have no pages.
     */
    public static function trackedModelPattern(): string
    {
        $namespace = config('stickle.namespaces.models');

        if (! is_string($namespace) || $namespace === '') {
            return '[^/]+';
        }

        try {
            $models = array_map(
                self::storeModelClass(...),
                self::getClassesWithTrait($namespace, StickleEntity::class)
            );
        } catch (Throwable) {
            return '[^/]+';
        }

        return $models === []
            ? '[^/]+'
            : implode('|', array_map(preg_quote(...), $models));
    }

    /**
     * resolveModelClass() for callers that answer a miss with a 404 or an empty
     * result rather than an error.
     *
     * @return class-string|null
     */
    public static function tryResolveModelClass(string $stored): ?string
    {
        $resolved = config('stickle.namespaces.models').'\\'.ucfirst($stored);

        if (! class_exists($resolved)) {
            return null;
        }

        /** @var class-string $resolved */
        return $resolved;
    }

    /**
     * Check if a class uses a specific trait (including parent classes)
     *
     * @param  string|object  $class  The class name or instance
     * @param  string  $trait  The fully qualified trait name
     */
    public static function usesTrait($class, string $trait): bool
    {
        if (is_string($class) && ! class_exists($class)) {
            return false;
        }

        $reflectionClass = new ReflectionClass($class);
        $traits = [];
        $currentClass = $reflectionClass;

        while ($currentClass) {
            $traits = array_merge($traits, array_keys($currentClass->getTraits()));
            $currentClass = $currentClass->getParentClass();
        }

        return in_array($trait, $traits);
    }

    /**
     * Get all classes within a namespace that use a specific trait
     *
     * @param  string  $namespace  The namespace to search in
     * @param  string  $trait  The fully qualified trait name
     * @return array<int, class-string> List of class names that use the specified trait
     */
    public static function getClassesWithTrait(string $namespace, string $trait): array
    {

        // Use Composer's PSR-4 mappings to find the correct directory for this namespace
        $directoryToScan = self::directoryFromNamespace($namespace);

        // Get all classes in the directory
        $allClasses = self::getClassesInDirectory($directoryToScan, $namespace);

        // Filter classes by namespace and trait
        $classesWithTrait = array_filter($allClasses, fn (string $className): bool =>
            // Check if class uses the trait
            self::usesTrait($className, $trait));

        $found = array_values($classesWithTrait);

        self::guardAgainstStoredNameCollisions($found);

        return $found;
    }

    /**
     * Refuse a set of tracked models that cannot be told apart once stored.
     *
     * model_class holds a basename, so App\Models\Thing and
     * App\Models\Vendor\Thing are the same string in every table -- their
     * attributes, audits, requests and statistics would share one bucket, and
     * resolveModelClass() could only ever hand back the first.
     *
     * Two classes directly under one namespace cannot share a name, so this was
     * unreachable while sub-namespaced models went undiscovered. Now that they
     * are found, silently merging them would be worse than not finding them.
     *
     * @param  array<int, class-string>  $classes
     */
    private static function guardAgainstStoredNameCollisions(array $classes): void
    {
        $byStoredName = [];

        foreach ($classes as $class) {
            $byStoredName[self::storeModelClass($class)][] = $class;
        }

        $collisions = array_filter($byStoredName, fn (array $names): bool => count($names) > 1);

        throw_unless(
            $collisions === [],
            RuntimeException::class,
            sprintf(
                'Tracked models share a model_class: %s. model_class stores a class basename, '
                .'so these cannot be told apart in any Stickle table. Rename one, move one out '
                .'of the tracked namespace, or register a morph map.',
                implode('; ', array_map(
                    fn (string $stored, array $names): string => sprintf('"%s" is %s', $stored, implode(' and ', $names)),
                    array_keys($collisions),
                    $collisions
                ))
            )
        );
    }

    /**
     * Get all classes in a directory
     *
     * @param  string  $directory  The directory to search in
     * @param  string  $appendNamespace  Optional namespace prefix for found classes
     * @return array<int, class-string> List of class names that use the specified trait
     */
    public static function getClassesInDirectory(string $directory, string $appendNamespace = ''): array
    {
        $classes = [];
        if (! is_dir($directory)) {
            return [];
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        // Filter for PHP files
        $phpFiles = new RegexIterator($files, '/\.php$/');

        foreach ($phpFiles as $phpFile) {
            $filePath = $phpFile->getRealPath();
            /**
             * getClassNameFromFile() returns the namespace declared in the
             * file, falling back to $appendNamespace only when the file
             * declares none. Prepending here as well would double it.
             */
            $classes[] = self::getClassNameFromFile($filePath, $appendNamespace);
        }

        $validClasses = array_filter($classes, fn (?string $className): bool => $className !== null && class_exists($className));

        // Cast to class-string array since we've verified classes exist
        return array_values($validClasses);
    }

    /**
     * Extract the class name from a PHP file
     *
     * @param  string  $filePath  Path to the PHP file
     * @param  string  $namespace  Optional namespace prefix
     * @return string|null The class name or null if not found
     */
    private static function getClassNameFromFile(string $filePath, string $namespace = ''): ?string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }
        $namespace = trim($namespace, '\\');

        $tokens = token_get_all($content);
        $count = count($tokens);
        $fileNamespace = '';
        $className = null;

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $i += 2;

                /**
                 * T_NAME_QUALIFIED is the whole of `App\Models\Vendor` in one
                 * token as of PHP 8.0. Matching only the PHP 7 spelling --
                 * T_STRING and T_NS_SEPARATOR in sequence -- left the parsed
                 * namespace empty for every file, so a class below the scanned
                 * root resolved to the wrong name and was silently discarded.
                 */
                while ($i < $count && in_array($tokens[$i][0], [T_NAME_QUALIFIED, T_STRING, T_NS_SEPARATOR], true)) {
                    $fileNamespace .= $tokens[$i][1];
                    $i++;
                }
            }

            if ($i < $count && $tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $className = $tokens[$j][1];
                        break 2;
                    }
                }
            }
        }

        if ($className) {
            if ($fileNamespace !== '' && $fileNamespace !== '0') {
                return $fileNamespace.'\\'.$className;
            }

            if ($namespace !== '' && $namespace !== '0') {
                return $namespace.'\\'.$className;
            }

            return $className;
        }

        return null;
    }

    /**
     * Check if a $class has a method returning a subtype of Illuminate\Database\Eloquent\Relations\Relation
     * that takes one of the $classes as an argument.
     *
     * Ie. This would return true becuase it returns a hasMany relationship with User as a parameter
     * public function users(): hasMany
     * {
     *    return $this->hasMany(User::class);
     *}
     *
     * @param  Application  $application  The Laravel application
     * @param  string  $class  The class name
     * @param  array<int, string>  $relationshipClasses  The relationship classes to check against
     * @param  array<int, string>  $relatedClasses  The related classes to check against
     * @return bool True if the class has a relationship with any of the specified classes, false otherwise
     */
    public static function hasRelationshipWith(Application $application, string $class, array $relationshipClasses, array $relatedClasses): bool
    {

        // Initialize the model inspector for the class
        $modelInspector = new ModelInspector($application);

        $info = $modelInspector->inspect(
            $class
        );

        // Get all the relations defined on the model
        $relations = $info['relations'];

        // Replace the fqcn with the class name of $relationshipClasses
        $relationshipClasses = array_map(class_basename(...), $relationshipClasses);

        // Check each relation to see if it relates to any of the specified classes
        foreach ($relations as $relation) {

            $type = $relation['type'];

            $related = $relation['related'];

            if (in_array($related, $relatedClasses) && in_array($type, $relationshipClasses)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Application  $application  The Laravel application
     * @param  string  $class  The class name
     * @param  array<int, string>  $relationshipClasses  The eloquent relationship classes to allow
     * @param  array<int, string>  $relatedClasses  The related classes to check against
     * @return array<int, array<string, mixed>> An array of Laravel Relationships
     */
    public static function getRelationshipsWith(Application $application, string $class, array $relationshipClasses, array $relatedClasses): array
    {

        // Initialize the model inspector for the class
        $modelInspector = new ModelInspector($application);

        $info = $modelInspector->inspect(
            $class
        );

        // Get all the relations defined on the model
        $relations = $info['relations'];

        // Replace the fqcn with the class name of $relationshipClasses
        $relationshipClasses = array_map(class_basename(...), $relationshipClasses);

        $return = [];

        // Check each relation to see if it relates to any of the specified classes
        foreach ($relations as $relation) {

            $type = $relation['type'];

            $related = $relation['related'];

            if (in_array($related, $relatedClasses) && in_array($type, $relationshipClasses)) {
                $return[] = $relation;
            }
        }

        return $return;
    }

    /**
     * @param  string  $class  The class name
     * @return array<string, mixed>
     */
    public static function getDefaultAttributesForClass(string $class): array
    {
        if (! class_exists($class)) {
            return [];
        }

        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getDefaultProperties();
    }

    /**
     * Convert a namespace to a filesystem directory path using Composer autoload config
     *
     * @param  string  $namespace  The namespace (e.g., 'App\Segments')
     * @return string The filesystem path
     */
    public static function directoryFromNamespace(string $namespace): string
    {

        $app = app();

        // @phpstan-ignore-next-line method.alreadyNarrowedType
        $basePath = method_exists($app, 'basePath') ? $app->basePath() : base_path();

        // Normalize the input namespace (remove leading/trailing backslashes)
        $namespace = trim($namespace, '\\');

        // Get PSR-4 mappings from Composer
        $psr4Mappings = self::getComposerPsr4Mappings($basePath);

        // Try to find a matching PSR-4 prefix by progressively removing segments
        $namespaceParts = explode('\\', $namespace);
        $bestMatch = ['prefix' => '', 'path' => ''];

        // Start with the full namespace and work backwards
        for ($i = count($namespaceParts); $i > 0; $i--) {
            $testNamespace = implode('\\', array_slice($namespaceParts, 0, $i)).'\\';
            if (isset($psr4Mappings[$testNamespace])) {
                $bestMatch = [
                    'prefix' => rtrim($testNamespace, '\\'),
                    'path' => is_array($psr4Mappings[$testNamespace])
                        ? $psr4Mappings[$testNamespace][0]
                        : $psr4Mappings[$testNamespace],
                ];
                break;
            }

        }

        if (empty($bestMatch['prefix'])) {
            // Fallback to old behavior if no PSR-4 match found
            return self::directoryFromNamespaceFallback($namespace, $basePath);
        }

        // Calculate the relative namespace path after the prefix
        $relativeNamespace = substr($namespace, strlen($bestMatch['prefix']));
        $relativeNamespace = ltrim($relativeNamespace, '\\');

        // Convert namespace to directory path
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace);

        // Combine base path, PSR-4 mapped path, and relative path
        $fullPath = rtrim($bestMatch['path'], DIRECTORY_SEPARATOR);

        if (! empty($relativePath)) {
            $fullPath .= DIRECTORY_SEPARATOR.$relativePath;
        }

        return $fullPath;
    }

    /**
     * Get PSR-4 autoload mappings from Composer's ClassLoader
     *
     * @param  string  $basePath  The application base path
     * @return array<string, string|array<string>> PSR-4 prefix => path mappings
     */
    private static function getComposerPsr4Mappings(string $basePath): array
    {
        // Try to get PSR-4 mappings from the already-loaded Composer ClassLoader
        $autoloadFunctions = spl_autoload_functions();

        foreach ($autoloadFunctions as $autoloadFunction) {
            if (is_array($autoloadFunction) && $autoloadFunction[0] instanceof ClassLoader) {
                $psr4Prefixes = $autoloadFunction[0]->getPrefixesPsr4();
                if (! empty($psr4Prefixes)) {
                    return $psr4Prefixes;
                }
            }
        }

        // Fallback: read from composer.json
        $composerJsonPath = $basePath.DIRECTORY_SEPARATOR.'composer.json';

        if (! file_exists($composerJsonPath)) {
            return [];
        }

        $composerContent = file_get_contents($composerJsonPath);
        if ($composerContent === false) {
            return [];
        }

        $composerData = json_decode($composerContent, true);

        $mappings = [];

        // Include autoload PSR-4 mappings
        if (isset($composerData['autoload']['psr-4'])) {
            $mappings = array_merge($mappings, $composerData['autoload']['psr-4']);
        }

        // Include autoload-dev PSR-4 mappings
        if (isset($composerData['autoload-dev']['psr-4'])) {
            return array_merge($mappings, $composerData['autoload-dev']['psr-4']);
        }

        return $mappings;
    }

    /**
     * Fallback method for namespace to directory conversion
     */
    private static function directoryFromNamespaceFallback(string $namespace, string $basePath): string
    {
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        $srcPaths = ['src', 'app'];

        foreach ($srcPaths as $srcPath) {
            $fullPath = $basePath.DIRECTORY_SEPARATOR.$srcPath.DIRECTORY_SEPARATOR.$namespacePath;
            if (is_dir($fullPath)) {
                return $fullPath;
            }
        }

        return $basePath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$namespacePath;
    }
}
