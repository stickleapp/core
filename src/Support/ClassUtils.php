<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

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
     * @return array List of class names that use the specified trait
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
     * @return array List of class names in the directory
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

        return $classes;
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
}
