<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade;

use Dclaysmith\LaravelCascade\Events\Group;
use Dclaysmith\LaravelCascade\Events\Identify;
use Dclaysmith\LaravelCascade\Events\Page;
use Dclaysmith\LaravelCascade\Events\Track;
use Dclaysmith\LaravelCascade\Listeners\AuthenticatableEventListener;
use Dclaysmith\LaravelCascade\Listeners\GroupListener;
use Dclaysmith\LaravelCascade\Listeners\IdentifyListener;
use Dclaysmith\LaravelCascade\Listeners\PageListener;
use Dclaysmith\LaravelCascade\Listeners\TrackListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Page::class => [
            PageListener::class,
        ],
        Group::class => [
            GroupListener::class,
        ],
        Track::class => [
            TrackListener::class,
        ],
        Identify::class => [
            IdentifyListener::class,
        ],
    ];

    /** @var array<string> */
    protected $subscribe = [
        AuthenticatableEventListener::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
