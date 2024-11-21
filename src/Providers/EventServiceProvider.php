<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Providers;

use Dclaysmith\LaravelCascade\Events\RequestReceived;
use Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener;
use Dclaysmith\LaravelCascade\Listeners\RequestReceivedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    // protected $listen = [
    //     RequestReceived::class => [
    //         RequestReceivedListener::class,
    //     ],
    // ];

    // protected $subscribe = [
    //     AuthenticatableEventListener::class,
    // ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
