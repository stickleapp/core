<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Track;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthenticatableEventListener implements ShouldQueue
{
    /**
     * @var LaravelRepository
     */
    protected $repository;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request, AnalyticsRepository $repository)
    {
        $this->repository = $repository;
        $this->request = $request;
    }

    public function onEvent($event): void
    {
        Log::debug('AuthenticatableEventListener->onEvent', [$event]);

        if (! $event->user) {
            return;
        }

        Track::dispatch([
            'model' => get_class($event->user),
            'objectUid' => $event->user->id,
            'sessionUid' => $this->request->session()->getId(),
            'event' => get_class($event), // Auth + Login
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {

        $events->listen(
            Authenticated::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            CurrentDeviceLogout::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Login::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Logout::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            OtherDeviceLogout::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            PasswordReset::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Registered::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Validated::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );

        $events->listen(
            Verified::class,
            '\Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener@onEvent'
        );
    }
}
