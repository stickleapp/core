<?php

declare(strict_types=1);

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
    public function __construct(public readonly AnalyticsRepositoryContract $repository) {}

    public function handle(Page $event): void
    {
        Log::debug('PageListener->handle()', [$event]);

        $this->repository->saveRequest(
            model: Arr::get($event->payload, 'model_class'),
            objectUid: Arr::get($event->payload, 'object_uid'),
            sessionUid: Arr::get($event->payload, 'session_uid'),
            timestamp: Arr::get($event->payload, 'timestamp', new DateTime),
            properties: [
                'url' => Arr::get($event->payload, 'url'),
                'path' => Arr::get($event->payload, 'path'),
                'host' => Arr::get($event->payload, 'host'),
                'search' => Arr::get($event->payload, 'search'),
                'query_params' => Arr::get($event->payload, 'query_params'),
                'utm_source' => Arr::get($event->payload, 'utm_source'),
                'utm_medium' => Arr::get($event->payload, 'utm_medium'),
                'utm_campaign' => Arr::get($event->payload, 'utm_campaign'),
                'utm_content' => Arr::get($event->payload, 'utm_content'),
            ]
        );
    }
}
