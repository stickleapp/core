<?php

use Illuminate\Auth\Events\PasswordReset;
use StickleApp\Core\Contracts\AnalyticsRepository;
use StickleApp\Core\Listeners\AuthenticatableEventListener;

it('can be instantiated', function () {

    /**
     * @var Illuminate\Http\Request
     */
    $request = Mockery::mock(Illuminate\Http\Request::class);

    /**
     * @var AnalyticsRepository
     */
    $repository = Mockery::mock(AnalyticsRepository::class);
    $listener = new AuthenticatableEventListener($request, $repository);
    expect($listener)->toBeInstanceOf(AuthenticatableEventListener::class);
});

// it('handles an event', function () {
//     $request = Mockery::mock(Illuminate\Http\Request::class);
//     $repository = Mockery::mock(AnalyticsRepository::class);
//     $listener = new AuthenticatableEventListener($request, $repository);
//     $event = Mockery::mock(PasswordReset::class);

//     // Assuming the handle method exists and takes an event as a parameter
//     $listener->handle($event);

//     // Add assertions to verify the expected behavior
//     // For example:
//     // expect($event->someProperty)->toBe(someExpectedValue);
// });
