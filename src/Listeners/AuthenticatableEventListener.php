<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use DateTime;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Validated;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;

class AuthenticatableEventListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public Request $request, public AnalyticsRepositoryContract $repository) {}

    public function onEvent(mixed $event): void
    {
        Log::debug('AuthenticatableEventListener->onEvent', [$event]);

        if (! $event->user) {
            return;
        }

        $timestamp = new DateTime;

        $this->repository->saveEvent(
            model: class_basename($event->user),
            objectUid: (string) $event->user->id,
            sessionUid: $this->request->session()->getId(),
            timestamp: $timestamp,
            event: get_class($event) ?: 'UnknownEvent',
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $eventClasses = [
            'Authenticated' => Authenticated::class,
            'CurrentDeviceLogout' => CurrentDeviceLogout::class,
            'Login' => Login::class,
            'Logout' => Logout::class,
            'OtherDeviceLogout' => OtherDeviceLogout::class,
            'PasswordReset' => PasswordReset::class,
            'Registered' => Registered::class,
            'Validated' => Validated::class,
            'Verified' => Verified::class,
        ];

        $trackedEvents = config('stickle.tracking.server.authenticationEventsTracked', []);

        foreach ($trackedEvents as $eventName) {
            if (isset($eventClasses[$eventName])) {
                $events->listen(
                    $eventClasses[$eventName],
                    [AuthenticatableEventListener::class, 'onEvent']
                );
            }
        }
    }
}
