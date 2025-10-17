<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Listeners\AuthenticatableEventListener;

it('can be instantiated', function (): void {

    /**
     * @var Request
     */
    $mock = Mockery::mock(Request::class);

    /**
     * @var AnalyticsRepositoryContract
     */
    $repository = Mockery::mock(AnalyticsRepositoryContract::class);
    $listener = new AuthenticatableEventListener($mock, $repository);
    expect($listener)->toBeInstanceOf(AuthenticatableEventListener::class);
});

// it('handles an event', function () {
//     $request = Mockery::mock(Illuminate\Http\Request::class);
//     $repository = Mockery::mock(AnalyticsRepositoryContract::class);
//     $listener = new AuthenticatableEventListener($request, $repository);
//     $event = Mockery::mock(PasswordReset::class);

//     // Assuming the handle method exists and takes an event as a parameter
//     $listener->handle($event);

//     // Add assertions to verify the expected behavior
//     // For example:
//     // expect($event->someProperty)->toBe(someExpectedValue);
// });
