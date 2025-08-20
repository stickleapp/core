<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Events\Page;
use StickleApp\Core\Models\Request;

class PageListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public readonly AnalyticsRepositoryContract $repository) {}

    public function handle(Page $event): void
    {
        Log::debug('PageListener->handle()', [$event]);

        $request = Request::create([
            'type' => 'page',
            'model_class' => $event->payload->model_class,
            'object_uid' => $event->payload->object_uid,
            'session_uid' => $event->payload->session_uid,
            'ip_address' => $event->payload->ip_address,
            'timestamp' => $event->payload->timestamp,
            'properties' => $event->payload->properties ?? [],
        ]);

    }
}
