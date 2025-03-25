<?php

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
}
