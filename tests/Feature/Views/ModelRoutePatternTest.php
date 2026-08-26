<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use StickleApp\Core\Support\ClassUtils;

/**
 * The pattern is built while routes register, including from composer scripts
 * that boot before config/stickle.php exists. An unset namespace once raised
 * "getClassesWithTrait(): Argument #1 ($namespace) must be of type string,
 * null given" and took the whole route file down with it, so every failure
 * mode has to degrade to the unconstrained pattern instead.
 */
it('matches the models that use the trait', function (): void {

    expect(ClassUtils::trackedModelPattern())
        ->toContain('User')
        ->toContain('Customer')
        ->not->toBe('[^/]+');
});

it('falls back to the unconstrained pattern when the namespace is unset', function (): void {

    config()->set('stickle.namespaces.models');

    expect(ClassUtils::trackedModelPattern())->toBe('[^/]+');
});

it('falls back to the unconstrained pattern when the namespace cannot be scanned', function (): void {

    config()->set('stickle.namespaces.models', 'No\\Such\\Namespace');

    expect(ClassUtils::trackedModelPattern())->toBe('[^/]+');
});

it('constrains the registered model route', function (): void {

    $route = collect(resolve(Router::class)->getRoutes())
        ->first(fn ($route): bool => $route->getName() === 'stickle::models');

    expect($route)->not->toBeNull()
        ->and($route->wheres['modelClass'] ?? '')->toContain('User');
});
