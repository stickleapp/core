<?php

namespace StickleApp\Core\Listeners;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        Log::debug('PageEvent Handled', [$event]);

        $this->repository->saveRequest(
            model: data_get($event->data, 'model'),
            objectUid: data_get($event->data, 'object_uid'),
            sessionUid: data_get($event->data, 'session_uid'),
            timestamp: data_get($event->data, 'timestamp', new DateTime),
            url: data_get($event->data, 'url'),
            path: data_get($event->data, 'path'),
            host: data_get($event->data, 'host'),
            search: data_get($event->data, 'search'),
            queryParams: data_get($event->data, 'query_params'),
            utmSource: data_get($event->data, 'utm_source'),
            utmMedium: data_get($event->data, 'utm_medium'),
            utmCampaign: data_get($event->data, 'utm_campaign'),
            utmContent: data_get($event->data, 'utm_content')
        );
    }
}
