<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RegexIterator;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Foundation\Application;
use ReflectionClass;

class ClassUtils
{
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
        // Determine base directory (adjust as needed for your project structure)
        $baseDir = dirname(__DIR__, 2);

        // Convert namespace to potential relative path
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        $searchDir = $baseDir.DIRECTORY_SEPARATOR.$namespacePath;

        // If the specific namespace directory doesn't exist, fall back to scanning from the base
        $directoryToScan = is_dir($searchDir) ? $searchDir : $baseDir;

        // Get all classes in the directory
        $allClasses = self::getClassesInDirectory($directoryToScan, $namespace);

        // Filter classes by namespace and trait
        $classesWithTrait = array_filter($allClasses, fn(string $className): bool =>
            // Check if class uses the trait
            self::usesTrait($className, $trait));

        return array_values($classesWithTrait);
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
            $className = self::getClassNameFromFile($filePath);
            if (strlen($appendNamespace) !== 0) {
                $className = $appendNamespace.'\\'.self::getClassNameFromFile($filePath);
            }
            $classes[] = $className;
        }

        $validClasses = array_filter($classes, fn(?string $className): bool => $className !== null && class_exists($className));

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
                while ($i < $count && ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR)) {
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
     * @param Application $application The Laravel application
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
        $relationshipClasses = array_map(fn(string $class): string => class_basename($class), $relationshipClasses);

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
     * @param Application $application The Laravel application
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
        $relationshipClasses = array_map(fn(string $class): string => class_basename($class), $relationshipClasses);

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
