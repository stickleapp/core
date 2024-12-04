<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PageListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(readonly AnalyticsRepository $repository) {}

    public function handle(Page $event): void
    {
        Log::debug('PageEvent Handled', [$event]);

        // $this->repository->saveRequest(
        //     model: data_get($event->data, 'model'),
        //     objectUid: data_get($event->data, 'object_uid'),
        //     sessionUid: data_get($event->data, 'session_uid'),
        //     ...
        // );
    }
}
