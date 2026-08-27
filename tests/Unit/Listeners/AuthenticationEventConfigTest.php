<?php

declare(strict_types=1);

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Listeners\AuthenticatableEventListener;

function subscribeWithTrackedEvents(array $trackedEvents): Dispatcher
{
    config()->set('stickle.tracking.server.authenticationEventsTracked', $trackedEvents);

    $dispatcher = new Dispatcher(app());

    $listener = new AuthenticatableEventListener(
        Mockery::mock(Request::class),
        Mockery::mock(AnalyticsRepositoryContract::class)
    );

    $listener->subscribe($dispatcher);

    return $dispatcher;
}

/**
 * A boolean in STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS_TRACKED parses to
 * the list ['1']: non-empty, so the subscriber registers, but matching no
 * event class, so nothing is listened for. That misconfiguration produced a
 * month of zero event rows with nothing reporting a problem.
 */
test('a boolean in the tracked event list is rejected rather than silently listening for nothing', function (): void {
    subscribeWithTrackedEvents(['1']);
})->throws(InvalidArgumentException::class, '1');

test('an unrecognised event name names itself and the valid set', function (): void {
    try {
        subscribeWithTrackedEvents(['Login', 'LoggedIn']);
        $this->fail('Expected an InvalidArgumentException.');
    } catch (InvalidArgumentException $invalidArgumentException) {
        expect($invalidArgumentException->getMessage())
            ->toContain('LoggedIn')
            ->toContain('CurrentDeviceLogout')
            ->not->toContain('[Login]');
    }
});

test('a valid list registers a listener for each named event', function (): void {
    $dispatcher = subscribeWithTrackedEvents(['Login', 'Logout']);

    expect($dispatcher->hasListeners(Login::class))->toBeTrue()
        ->and($dispatcher->hasListeners(Verified::class))->toBeFalse();
});
