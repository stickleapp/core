<?php

declare(strict_types=1);

namespace StickleApp\Core;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Override;
use StickleApp\Core\Events\Page;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Listeners\AuthenticatableEventListener;
use StickleApp\Core\Listeners\PageListener;
use StickleApp\Core\Listeners\TrackListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Laravel reads this PROPERTY in EventServiceProvider::register(). A method
     * returning subscribers is Symfony's convention and is never called here.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        AuthenticatableEventListener::class,
    ];

    protected $listen = [
        Page::class => [
            PageListener::class,
        ],
        Track::class => [
            TrackListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    #[Override]
    public function boot(): void
    {
        parent::boot();
    }
}
