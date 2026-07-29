<?php

declare(strict_types=1);

use Illuminate\Auth\Access\Gate as GateInstance;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use StickleApp\Core\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Replace the container's Gate with an empty one, so no ability is defined.
 *
 * Laravel's Gate can define abilities but not forget them, and TestCase
 * defines viewStickle for the rest of the suite. This is how a test asserts
 * the state a fresh install is in.
 */
function withoutStickleGate(): void
{
    app()->singleton(
        GateContract::class,
        fn ($app): GateInstance => new GateInstance($app, fn () => $app['auth']->user())
    );
}
