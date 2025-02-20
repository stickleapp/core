<?php

namespace StickleApp\Core\Listeners;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepository;
use StickleApp\Core\Events\Page;

class PageListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(readonly AnalyticsRepository $repository) {}

    public function handle(Page $event): void
    {
        Log::debug('PageListener->handle()', [$event]);

        $this->repository->saveRequest(
            model: Arr::get($event->data, 'model'),
            objectUid: Arr::get($event->data, 'object_uid'),
            sessionUid: Arr::get($event->data, 'session_uid'),
            timestamp: Arr::get($event->data, 'timestamp', new DateTime),
            url: Arr::get($event->data, 'url'),
            path: Arr::get($event->data, 'path'),
            host: Arr::get($event->data, 'host'),
            search: Arr::get($event->data, 'search'),
            queryParams: Arr::get($event->data, 'query_params'),
            utmSource: Arr::get($event->data, 'utm_source'),
            utmMedium: Arr::get($event->data, 'utm_medium'),
            utmCampaign: Arr::get($event->data, 'utm_campaign'),
            utmContent: Arr::get($event->data, 'utm_content')
        );
    }
}
