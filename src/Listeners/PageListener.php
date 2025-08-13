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
            type: 'track',
            modelClass: Arr::get($event->payload, 'model_class'),
            objectUid: Arr::get($event->payload, 'object_uid'),
            sessionUid: Arr::get($event->payload, 'session_uid'),
            ipAddress: data_get($event->payload, 'ip_address'),
            timestamp: Arr::get($event->payload, 'timestamp', new DateTime),
            properties: Arr::get($event->payload, 'properties', [])
        );
    }
}
