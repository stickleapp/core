<?php

declare(strict_types=1);

use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Support\AttributeUtils;
use StickleApp\Core\Tests\Fixtures\Attributes\AnnotatedProperty;
use Workbench\App\Models\User;

/**
 * D1: the scanner filtered on ReflectionMethod::IS_PUBLIC and on the legacy
 * get*Attribute name, so a Laravel 9+ accessor -- protected, and named for the
 * attribute -- was invisible. Every annotation in the package's own workbench
 * is of that modern form.
 */
test('the method scanner finds an annotation on a modern protected accessor', function (): void {
    $metadata = AttributeUtils::getAllAttributesForClass_targetMethod(
        User::class,
        StickleAttributeMetadata::class
    );

    expect($metadata)->toHaveKey('user_rating');
});

/**
 * D2: the result was nested under the attribute class name, so the only key
 * present was StickleAttributeMetadata::class and a lookup by attribute name
 * always missed.
 */
test('the method scanner keys metadata by attribute name, not by attribute class', function (): void {
    $metadata = AttributeUtils::getAllAttributesForClass_targetMethod(
        User::class,
        StickleAttributeMetadata::class
    );

    expect($metadata)->not->toHaveKey(StickleAttributeMetadata::class)
        ->and($metadata['user_rating']['label'])->toBe('User Rating');
});

test('the method scanner normalises camel case and digits to snake case', function (): void {
    $metadata = AttributeUtils::getAllAttributesForClass_targetMethod(
        User::class,
        StickleAttributeMetadata::class
    );

    expect($metadata)->toHaveKey('tickets_resolved_last_30_days')
        ->and($metadata)->toHaveKey('average_resolution_time_7_days');
});

/**
 * The class ships two scanners and merges their results, so metadata on a
 * property is clearly intended to work. StickleAttributeMetadata declared
 * only Attribute::TARGET_METHOD, so the property scanner could never return
 * anything -- newInstance() rejects the target.
 */
test('the property scanner reads an annotation declared on a property', function (): void {
    $metadata = AttributeUtils::getAllAttributesForClass_targetProperty(
        AnnotatedProperty::class,
        StickleAttributeMetadata::class
    );

    expect($metadata)->toHaveKey('seat_count')
        ->and($metadata['seat_count']['label'])->toBe('Seat Count');
});
