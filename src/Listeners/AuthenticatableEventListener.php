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
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            CurrentDeviceLogout::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            Login::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            Logout::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            OtherDeviceLogout::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            PasswordReset::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            Registered::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            Validated::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );

        $events->listen(
            Verified::class,
            [AuthenticatableEventListener::class, 'onEvent']
        );
    }
}
