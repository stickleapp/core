<?php

declare(strict_types=1);

namespace StickleApp\Core;

use StickleApp\\Core\Core\Events\Group;
use StickleApp\\Core\Core\Events\Identify;
use StickleApp\\Core\Core\Events\Page;
use StickleApp\\Core\Core\Events\Track;
use StickleApp\\Core\Core\Listeners\AuthenticatableEventListener;
use StickleApp\\Core\Core\Listeners\GroupListener;
use StickleApp\\Core\Core\Listeners\IdentifyListener;
use StickleApp\\Core\Core\Listeners\PageListener;
use StickleApp\\Core\Core\Listeners\TrackListener;
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
