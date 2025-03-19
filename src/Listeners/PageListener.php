<?php

namespace StickleApp\Core\Listeners;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Events\Page;

class PageListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(readonly AnalyticsRepositoryContract $repository) {}

    public function handle(Page $event): void
    {
        Log::debug('PageListener->handle()', [$event]);

        $this->repository->saveRequest(
            model: Arr::get($event->payload, 'model'),
            objectUid: Arr::get($event->payload, 'object_uid'),
            sessionUid: Arr::get($event->payload, 'session_uid'),
            timestamp: Arr::get($event->payload, 'timestamp', new DateTime),
            url: Arr::get($event->payload, 'url'),
            path: Arr::get($event->payload, 'path'),
            host: Arr::get($event->payload, 'host'),
            search: Arr::get($event->payload, 'search'),
            queryParams: Arr::get($event->payload, 'query_params'),
            utmSource: Arr::get($event->payload, 'utm_source'),
            utmMedium: Arr::get($event->payload, 'utm_medium'),
            utmCampaign: Arr::get($event->payload, 'utm_campaign'),
            utmContent: Arr::get($event->payload, 'utm_content')
        );
    }
}
