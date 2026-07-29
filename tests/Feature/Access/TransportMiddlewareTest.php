<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    putenv('STICKLE_WEB_MIDDLEWARE=auth');
    putenv('STICKLE_API_MIDDLEWARE=auth');
});

afterEach(function (): void {
    putenv('STICKLE_WEB_MIDDLEWARE');
    putenv('STICKLE_API_MIDDLEWARE');
});

it('cannot be opened by emptying the configured middleware', function (): void {

    config()->set('stickle.routes.web.middleware', []);

    withoutStickleGate();

    $this->get('/stickle/live')->assertForbidden();
});

it('cannot be opened by unsetting the configured middleware', function (): void {

    config()->set('stickle.routes.web.middleware');

    withoutStickleGate();

    $this->get('/stickle/live')->assertForbidden();
});

it('composes the configured transport middleware ahead of the guard', function (): void {

    $route = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($route): bool => $route->uri() === 'stickle/live');

    expect($route)->not->toBeNull();

    $middleware = $route->gatherMiddleware();

    // The configured list supplies transport and the route file appends the
    // guard after it. An application adding 'auth' to that list therefore gets
    // it applied ahead of can:viewStickle, which is the whole reason these
    // config keys were kept.
    expect($middleware)->toContain('web')
        ->and($middleware)->toContain('can:viewStickle')
        ->and(array_search('can:viewStickle', $middleware, true))
        ->toBeGreaterThan(array_search('web', $middleware, true));
});

it('does not read the removed environment variables', function (): void {

    $config = require __DIR__.'/../../../config/stickle.php';

    expect($config['routes']['web']['middleware'])->toBe(['web']);
    expect($config['routes']['api']['middleware'])->toBe(['api']);
});
