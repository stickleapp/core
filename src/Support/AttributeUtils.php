<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

use Attribute;
use Exception;
use ReflectionClass;
use ReflectionMethod;

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
     * @return array<''|class-string, non-empty-array<string, mixed>>
     */
    public static function getAllAttributesForClass_targetMethod(string $className, ?string $attributeClass = null): array
    {
        throw_unless(class_exists($className), Exception::class, 'Class not found');

        throw_if($attributeClass !== null && ! class_exists($attributeClass), Exception::class, 'Attribute not found');

        $reflectionClass = new ReflectionClass($className);
        $metadata = [];

        // Check methods for the attribute
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Match for accessor methods (get*Attribute)
            if (preg_match('/^get(.+)Attribute$/', $methodName, $matches)) {

                $attributeName = lcfirst($matches[1]);

                // First, add underscore before each uppercase letter
                $pattern = '/(?<!^)[A-Z]/';
                $result = preg_replace($pattern, '_$0', $attributeName);
                $attributeName = $result ?? $attributeName;

                // Then add underscore before numeric sequences
                $pattern = '/([a-zA-Z])(\d+)/';
                $result = preg_replace($pattern, '$1_$2', $attributeName);
                $attributeName = $result ?? $attributeName;

                $attributeName = strtolower($attributeName);

                $attributes = $method->getAttributes($attributeClass);

                if (! empty($attributes)) {
                    $instance = $attributes[0]->newInstance();
                    if (property_exists($instance, 'value')) {
                        $metadata[$attributeClass][$attributeName] = $instance->value;
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * @return array<''|class-string, non-empty-array<string, mixed>>
     */
    public static function getAllAttributesForClass_targetProperty(string $className, ?string $attributeClass = null): array
    {
        throw_unless(class_exists($className), Exception::class, 'Class not found');

        throw_if($attributeClass !== null && ! class_exists($attributeClass), Exception::class, 'Attribute not found');

        $reflectionClass = new ReflectionClass($className);
        $metadata = [];

        // Check properties for the attribute (if needed)
        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes($attributeClass);
            if (! empty($attributes)) {
                $instance = $attributes[0]->newInstance();
                if (property_exists($instance, 'value')) {
                    $metadata[$attributeClass][$property->getName()] = $instance->value;
                }
            }
        }

        return $metadata;
    }

    public static function getAttributeForClass(string $className, string $attributeClass): mixed
    {
        $attributes = self::getAllAttributesForClass_targetClass($className, $attributeClass);

        return data_get($attributes, $attributeClass, null);
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
