<?php

declare(strict_types=1);

use StickleApp\Core\Commands\InstallCommand;
use StickleApp\Core\Listeners\AuthenticatableEventListener;

function defaultTrackedAuthenticationEvents(): array
{
    $config = require __DIR__.'/../../../config/stickle.php';

    return array_values($config['tracking']['server']['authenticationEventsTracked']);
}

/**
 * Laravel dispatches Authenticated on every request that resolves a user, and
 * Validated on every credential check, so tracking either roughly doubles
 * stc_requests for signal the request rows already carry.
 */
test('the default tracked event list excludes the per-request events', function (): void {
    expect(defaultTrackedAuthenticationEvents())
        ->not->toContain('Authenticated')
        ->not->toContain('Validated');
});

test('the default tracked event list keeps every once-per-session event', function (): void {
    expect(defaultTrackedAuthenticationEvents())->toBe([
        'CurrentDeviceLogout',
        'Login',
        'Logout',
        'OtherDeviceLogout',
        'PasswordReset',
        'Registered',
        'Verified',
    ]);
});

/**
 * Authenticated and Validated stay subscribable -- they are just not defaults.
 * The installer writes the recommended list, so it has to be the same one.
 */
test('the installer writes the config default', function (): void {
    $reflectionClass = new ReflectionClass(InstallCommand::class);

    expect($reflectionClass->getConstant('AUTHENTICATION_EVENTS'))
        ->toBe(defaultTrackedAuthenticationEvents());
});

test('every event the installer writes is one the listener recognises', function (): void {
    $reflectionClass = new ReflectionClass(InstallCommand::class);

    expect(array_diff(
        $reflectionClass->getConstant('AUTHENTICATION_EVENTS'),
        array_keys(AuthenticatableEventListener::EVENT_CLASSES)
    ))->toBe([]);
});
