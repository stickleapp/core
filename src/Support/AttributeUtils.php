<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

use Exception;
use ReflectionClass;

class AttributeUtils
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function getAllAttributesForClass_targetClass(string $className, ?string $attributeClass = null): array
    {
        throw_unless(class_exists($className), Exception::class, 'Class not found');

        throw_if($attributeClass !== null && ! class_exists($attributeClass), Exception::class, 'Attribute not found');

        $reflectionClass = new ReflectionClass($className);
        $metadata = [];

        // Check class for the attribute
        $classAttributes = $reflectionClass->getAttributes($attributeClass);

        foreach ($classAttributes as $classAttribute) {
            $instance = $classAttribute->newInstance();
            if (property_exists($instance, 'value')) {
                $metadata[$attributeClass] = $instance->value;
            }
        }

        return $metadata;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getAllAttributesForClass_targetMethod(string $className, ?string $attributeClass = null): array
    {
        throw_unless(class_exists($className), Exception::class, 'Class not found');

        throw_if($attributeClass !== null && ! class_exists($attributeClass), Exception::class, 'Attribute not found');

        $reflectionClass = new ReflectionClass($className);
        $metadata = [];

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $attributes = $reflectionMethod->getAttributes($attributeClass);

            if ($attributes === []) {
                continue;
            }

            $instance = $attributes[0]->newInstance();

            if (! property_exists($instance, 'value')) {
                continue;
            }

            $metadata[self::attributeNameFromMethod($reflectionMethod->getName())] = $instance->value;
        }

        return $metadata;
    }

    /**
     * Derive the tracked attribute name from an accessor method name.
     *
     * A Laravel 9+ accessor is named for the attribute (`userRating`) and is
     * usually protected; the legacy form wraps it (`getUserRatingAttribute`)
     * and must be public. Both reduce to the same camel form before being
     * normalised to the snake_case key `stickleTrackedAttributes()` uses.
     */
    private static function attributeNameFromMethod(string $methodName): string
    {
        $attributeName = preg_match('/^get(.+)Attribute$/', $methodName, $matches) === 1
            ? lcfirst($matches[1])
            : lcfirst($methodName);

        // First, add underscore before each uppercase letter
        $result = preg_replace('/(?<!^)[A-Z]/', '_$0', $attributeName);
        $attributeName = $result ?? $attributeName;

        // Then add underscore before numeric sequences
        $result = preg_replace('/([a-zA-Z])(\d+)/', '$1_$2', $attributeName);
        $attributeName = $result ?? $attributeName;

        return strtolower($attributeName);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getAllAttributesForClass_targetProperty(string $className, ?string $attributeClass = null): array
    {
        throw_unless(class_exists($className), Exception::class, 'Class not found');

        throw_if($attributeClass !== null && ! class_exists($attributeClass), Exception::class, 'Attribute not found');

        $reflectionClass = new ReflectionClass($className);
        $metadata = [];

        // Check properties for the attribute (if needed)
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes($attributeClass);
            if (! empty($attributes)) {
                $instance = $attributes[0]->newInstance();
                if (property_exists($instance, 'value')) {
                    $metadata[$reflectionProperty->getName()] = $instance->value;
                }
            }
        }

        return $metadata;
    }

    public static function getAttributeForClass(string $className, string $attributeClass): mixed
    {
        $attributes = self::getAllAttributesForClass_targetClass($className, $attributeClass);

        return data_get($attributes, $attributeClass);
    }

    public static function getAttributeForMethod(string $className, string $methodName, string $attributeClass): mixed
    {
        throw_unless(class_exists($className), Exception::class, 'Class not found');

        throw_unless(class_exists($attributeClass), Exception::class, 'Attribute not found');

        $reflectionClass = new ReflectionClass($className);

        throw_unless($reflectionClass->hasMethod($methodName), Exception::class, 'Method not found');

        $reflectionMethod = $reflectionClass->getMethod($methodName);

        $attributes = $reflectionMethod->getAttributes($attributeClass);

        if (empty($attributes)) {
            return [];
        }

        return $attributes[0]->newInstance();
    }
}
