<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

use ReflectionClass;
use ReflectionMethod;

class AttributeUtils
{
    /**
     * Get all metadata for a specific attribute class in a target class
     *
     * @param  string  $className  The class to inspect
     * @param  string  $attributeClass  The attribute class to look for (e.g., StickleAttributeMetadata::class)
     * @return array<string, mixed> Map of attribute name to metadata value
     */
    public static function getAttributesForClass(string $className, string $attributeClass): array
    {

        if (! class_exists($className)) {
            throw new \Exception('Class not found');
        }

        if (! class_exists($attributeClass)) {
            throw new \Exception('Attribute not found');
        }

        $reflection = new ReflectionClass($className);
        $metadata = [];

        // Check methods for the attribute
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Match for accessor methods (get*Attribute)
            if (preg_match('/^get(.+)Attribute$/', $methodName, $matches)) {

                $attributeName = lcfirst($matches[1]);

                // First, add underscore before each uppercase letter
                $pattern = '/(?<!^)[A-Z]/';
                $attributeName = preg_replace($pattern, '_$0', $attributeName);

                // Then add underscore before numeric sequences
                $pattern = '/([a-zA-Z])(\d+)/';
                $attributeName = preg_replace($pattern, '$1_$2', $attributeName);

                $attributeName = strtolower($attributeName);

                $attributes = $method->getAttributes($attributeClass);

                if (! empty($attributes)) {
                    $metadata[$attributeName] = $attributes[0]->newInstance()->value;
                }
            }
        }
        // Check properties for the attribute (if needed)
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes($attributeClass);
            if (! empty($attributes)) {
                $metadata[$property->getName()] = $attributes[0]->newInstance()->value;
            }
        }

        return $metadata;
    }

    /**
     * Get metadata for a specific attribute in a class
     *
     * @param  string  $className  The class to inspect
     * @param  string  $attributeClass  The attribute class to look for
     * @param  string  $attributeName  The attribute name to find
     * @return array<string, mixed>|null The metadata or null if not found
     */
    public static function getAttributeForClass(string $className, string $attributeClass, string $attributeName): ?array
    {
        $allAttributes = self::getAttributesForClass($className, $attributeClass);

        return $allAttributes[$attributeName] ?? null;
    }

    /**
     * Get all metadata for a specific attribute class in an object instance
     *
     * @param  object  $object  The object to inspect
     * @param  string  $attributeClass  The attribute class to look for
     * @return array<string, mixed> Map of attribute name to metadata value
     */
    public static function getAttributesForObject(object $object, string $attributeClass): array
    {
        return self::getAttributesForClass(get_class($object), $attributeClass);
    }

    /**
     * Get metadata for a specific attribute in an object
     *
     * @param  object  $object  The object to inspect
     * @param  string  $attributeClass  The attribute class to look for
     * @param  string  $attributeName  The attribute name to find
     * @return array<string, mixed>|null The metadata or null if not found
     */
    public static function getAttributeForObject(object $object, string $attributeClass, string $attributeName): ?array
    {
        return self::getAttributeForClass(get_class($object), $attributeClass, $attributeName);
    }
}
