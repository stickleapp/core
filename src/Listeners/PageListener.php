<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PageListener implements ShouldQueue
{
    /**
     * @var AnalyticsRepository
     */
    protected $repository;

    /**
     * Create the event listener.
     */
    public function __construct(AnalyticsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(Page $event): void
    {
        Log::debug('PageEvent Handled', [$event]);
        //

        // $this->repository->saveEvent(
        //     model: data_get($event->data, 'model'),
        //     objectUid: data_get($event->data, 'object_uid'),
        //     sessionUid: data_get($event->data, 'session_uid'),
        //     event: data_get($event->data, 'event'),
        // );
    }
}
