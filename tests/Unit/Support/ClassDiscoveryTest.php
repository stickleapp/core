<?php

declare(strict_types=1);

use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Tests\Fixtures\CollidingModels\Thing;
use StickleApp\Core\Tests\Fixtures\DiscoveryModels\TopLevel;
use StickleApp\Core\Tests\Fixtures\DiscoveryModels\Vendor\Nested;
use StickleApp\Core\Traits\StickleEntity;

const DISCOVERY_NAMESPACE = 'StickleApp\Core\Tests\Fixtures\DiscoveryModels';

/**
 * getClassNameFromFile() read the namespace by walking T_STRING and
 * T_NS_SEPARATOR tokens after T_NAMESPACE. Since PHP 8.0 a qualified name is a
 * single T_NAME_QUALIFIED token, so that walk matched nothing and the parsed
 * namespace was always empty. Callers papered over it by prepending the
 * configured namespace, which reconstructs the right name for a class sitting
 * directly under it -- and the wrong one for anything deeper, which then fails
 * class_exists() and is dropped without a word.
 */
test('discovery finds a tracked model in a sub-namespace', function (): void {
    $found = ClassUtils::getClassesInDirectory(
        dirname(__DIR__, 2).'/Fixtures/DiscoveryModels',
        DISCOVERY_NAMESPACE
    );

    expect($found)->toContain(Nested::class);
});

test('discovery still finds a tracked model directly under the namespace', function (): void {
    $found = ClassUtils::getClassesInDirectory(
        dirname(__DIR__, 2).'/Fixtures/DiscoveryModels',
        DISCOVERY_NAMESPACE
    );

    expect($found)->toContain(TopLevel::class);
});

test('getClassesWithTrait carries the sub-namespace through', function (): void {
    expect(ClassUtils::getClassesWithTrait(DISCOVERY_NAMESPACE, StickleEntity::class))
        ->toContain(Nested::class)
        ->toContain(TopLevel::class);
});

/**
 * model_class stores a basename, so two tracked models with the same short
 * name are indistinguishable once written -- their attributes, audits,
 * requests and statistics all land in one bucket.
 *
 * This could not happen while sub-namespaced models went undiscovered: two
 * classes directly under one namespace cannot share a name. Making them
 * discoverable makes it possible, so it has to be caught rather than written.
 * The real repair is a morph map (#53); until then this refuses to guess.
 */
test('two tracked models with the same basename are refused rather than conflated', function (): void {
    ClassUtils::getClassesWithTrait(
        'StickleApp\Core\Tests\Fixtures\CollidingModels',
        StickleEntity::class
    );
})->throws(RuntimeException::class, 'Thing');

test('the collision names both classes and points at the fix', function (): void {
    try {
        ClassUtils::getClassesWithTrait(
            'StickleApp\Core\Tests\Fixtures\CollidingModels',
            StickleEntity::class
        );
        $this->fail('Expected a RuntimeException.');
    } catch (RuntimeException $runtimeException) {
        expect($runtimeException->getMessage())
            ->toContain(Thing::class)
            ->toContain(\StickleApp\Core\Tests\Fixtures\CollidingModels\Vendor\Thing::class)
            ->toContain('model_class');
    }
});
