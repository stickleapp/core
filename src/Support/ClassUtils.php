<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

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

        $reflection = new ReflectionClass($class);
        $traits = [];
        $currentClass = $reflection;

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
        $classesWithTrait = array_filter($allClasses, function ($className) use ($trait) {

            // Check if class uses the trait
            return self::usesTrait($className, $trait);
        });

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

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        // Filter for PHP files
        $phpFiles = new \RegexIterator($files, '/\.php$/');

        foreach ($phpFiles as $file) {
            $filePath = $file->getRealPath();
            $className = self::getClassNameFromFile($filePath);
            if (strlen($appendNamespace)) {
                $className = $appendNamespace.'\\'.self::getClassNameFromFile($filePath);
            }
            $classes[] = $className;
        }

        $validClasses = array_filter($classes, function ($className) {
            return $className !== null && class_exists($className);
        });

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
            if ($fileNamespace) {
                return $fileNamespace.'\\'.$className;
            }

            if ($namespace) {
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
     * @param  \Illuminate\Foundation\Application  $app  The Laravel application
     * @param  string  $class  The class name
     * @param  array<int, string>  $relationshipClasses  The relationship classes to check against
     * @param  array<int, string>  $relatedClasses  The related classes to check against
     * @return bool True if the class has a relationship with any of the specified classes, false otherwise
     */
    public static function hasRelationshipWith(Application $app, string $class, array $relationshipClasses, array $relatedClasses): bool
    {

        // Initialize the model inspector for the class
        $inspector = new ModelInspector($app);

        $info = $inspector->inspect(
            $class
        );

        // Get all the relations defined on the model
        $relations = $info['relations'];

        // Replace the fqcn with the class name of $relationshipClasses
        $relationshipClasses = array_map(function ($class) {
            return class_basename($class);
        }, $relationshipClasses);

        // Check each relation to see if it relates to any of the specified classes
        foreach ($relations as $relationInfo) {

            $type = $relationInfo['type'];

            $related = $relationInfo['related'];

            if (in_array($related, $relatedClasses) && in_array($type, $relationshipClasses)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app  The Laravel application
     * @param  string  $class  The class name
     * @param  array<int, string>  $relationshipClasses  The eloquent relationship classes to allow
     * @param  array<int, string>  $relatedClasses  The related classes to check against
     * @return array<int, array<string, mixed>> An array of Laravel Relationships
     */
    public static function getRelationshipsWith(Application $app, string $class, array $relationshipClasses, array $relatedClasses): array
    {

        // Initialize the model inspector for the class
        $inspector = new ModelInspector($app);

        $info = $inspector->inspect(
            $class
        );

        // Get all the relations defined on the model
        $relations = $info['relations'];

        // Replace the fqcn with the class name of $relationshipClasses
        $relationshipClasses = array_map(function ($class) {
            return class_basename($class);
        }, $relationshipClasses);

        $return = [];

        // Check each relation to see if it relates to any of the specified classes
        foreach ($relations as $relationInfo) {

            $type = $relationInfo['type'];

            $related = $relationInfo['related'];

            if (in_array($related, $relatedClasses) && in_array($type, $relationshipClasses)) {
                $return[] = $relationInfo;
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

        $reflection = new ReflectionClass($class);

        return $reflection->getDefaultProperties();
    }

    /**
     * Convert a namespace to a filesystem directory path
     *
     * @param  string  $namespace  The namespace (e.g., 'App\Segments')
     * @return string The filesystem path (e.g., '/var/app/current/src/App/Segments')
     */
    public static function directoryFromNamespace(string $namespace): string
    {
        // Get the application base path
        $app = app();
        // @phpstan-ignore-next-line method.alreadyNarrowedType
        $basePath = method_exists($app, 'basePath') ? $app->basePath() : base_path();

        // Convert namespace separators to directory separators
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        // Determine the source directory based on common Laravel conventions
        $srcPaths = ['src', 'app'];

        foreach ($srcPaths as $srcPath) {
            $fullPath = $basePath.DIRECTORY_SEPARATOR.$srcPath.DIRECTORY_SEPARATOR.$namespacePath;
            if (is_dir($fullPath)) {
                return $fullPath;
            }
        }

        // If no existing directory found, default to src
        return $basePath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$namespacePath;
    }
}
