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

    public function handle(Page $page): void
    {
        Log::debug('PageListener->handle()', [$page]);

        Request::query()->create([
            'type' => 'page',
            'model_class' => $page->payload->model_class,
            'object_uid' => $page->payload->object_uid,
            'session_uid' => $page->payload->session_uid,
            'ip_address' => $page->payload->ip_address,
            'timestamp' => $page->payload->timestamp,
            'properties' => $page->payload->properties ?? [],
        ]);

    }
}
