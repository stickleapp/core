<?php

declare(strict_types=1);

namespace StickleApp\Core\Support;

use Attribute;
use ReflectionClass;
use ReflectionMethod;

class AttributeUtils
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function getAllAttributesForClass_targetClass(string $className, ?string $attributeClass = null): array
    {
        if (! class_exists($className)) {
            throw new \Exception('Class not found');
        }

        if ($attributeClass !== null) {
            if (! class_exists($attributeClass)) {
                throw new \Exception('Attribute not found');
            }
        }

        $reflection = new ReflectionClass($className);
        $metadata = [];

        // Check class for the attribute
        $classAttributes = $reflection->getAttributes($attributeClass);

        foreach ($classAttributes as $attribute) {
            $instance = $attribute->newInstance();
            $metadata[$attributeClass] = $instance->value;
        }

        return $metadata;
    }

    /**
     * @return array<''|class-string, non-empty-array<string, mixed>>
     */
    public static function getAllAttributesForClass_targetMethod(string $className, ?string $attributeClass = null): array
    {
        if (! class_exists($className)) {
            throw new \Exception('Class not found');
        }

        if ($attributeClass !== null) {
            if (! class_exists($attributeClass)) {
                throw new \Exception('Attribute not found');
            }
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
                    $metadata[$attributeClass][$attributeName] = $attributes[0]->newInstance()->value;
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
        if (! class_exists($className)) {
            throw new \Exception('Class not found');
        }

        if ($attributeClass !== null) {
            if (! class_exists($attributeClass)) {
                throw new \Exception('Attribute not found');
            }
        }

        $reflection = new ReflectionClass($className);
        $metadata = [];

        // Check properties for the attribute (if needed)
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes($attributeClass);
            if (! empty($attributes)) {
                $metadata[$attributeClass][$property->getName()] = $attributes[0]->newInstance()->value;
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
        if (! class_exists($className)) {
            throw new \Exception('Class not found');
        }

        if (! class_exists($attributeClass)) {
            throw new \Exception('Attribute not found');
        }

        $reflection = new ReflectionClass($className);

        if (! $reflection->hasMethod($methodName)) {
            throw new \Exception('Method not found');
        }

        $method = $reflection->getMethod($methodName);

        $attributes = $method->getAttributes($attributeClass);

        if (empty($attributes)) {
            return [];
        }

        return $attributes[0]->newInstance();
    }
}
