<?php

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
use StickleApp\Core\Contracts\AnalyticsRepository;

class AuthenticatableEventListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public Request $request, public AnalyticsRepository $repository) {}

    public function onEvent(mixed $event): void
    {
        Log::debug('AuthenticatableEventListener->onEvent', [$event]);

        if (! $event->user) {
            return;
        }

        $this->repository->saveEvent(
            model: get_class($event->user),
            objectUid: $event->user->id,
            sessionUid: $this->request->session()->getId(),
            timestamp: new DateTime,
            event: get_class($event),
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {

        $events->listen(
            Authenticated::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            CurrentDeviceLogout::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Login::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Logout::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            OtherDeviceLogout::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            PasswordReset::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Registered::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Validated::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Verified::class,
            '\StickleApp\Core\Listeners\AuthenticatableEventListener@onEvent'
        );
    }
}
