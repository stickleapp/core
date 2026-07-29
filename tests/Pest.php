<?php

declare(strict_types=1);

use Illuminate\Auth\Access\Gate as GateInstance;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Support\Facades\Facade;
use StickleApp\Core\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Replace the container's Gate with an empty one, so no ability is defined.
 *
 * Laravel's Gate can define abilities but not forget them, and TestCase
 * defines viewStickle for the rest of the suite. This is how a test asserts
 * the state a fresh install is in.
 *
 * The Gate facade caches its resolved root instance independently of the
 * container binding, so rebinding the singleton alone leaves any code that
 * reaches the Gate through the facade (rather than through fresh container
 * resolution, as the `can:` middleware does) still holding the old,
 * allowing instance. Clearing the facade's cache forces it to re-resolve
 * against the new binding.
 */
function withoutStickleGate(): void
{
    app()->singleton(
        GateContract::class,
        fn ($app): GateInstance => new GateInstance($app, fn () => $app['auth']->user())
    );

    Facade::clearResolvedInstance(GateContract::class);
}
