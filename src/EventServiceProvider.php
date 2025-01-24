<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use StickleApp\Core\Events\Group;
use StickleApp\Core\Events\Identify;
use StickleApp\Core\Events\Page;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Listeners\AuthenticatableEventListener;
use StickleApp\Core\Listeners\GroupListener;
use StickleApp\Core\Listeners\IdentifyListener;
use StickleApp\Core\Listeners\PageListener;
use StickleApp\Core\Listeners\TrackListener;

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
