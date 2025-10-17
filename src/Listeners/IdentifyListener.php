<?php

declare(strict_types=1);

namespace StickleApp\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;
use StickleApp\Core\Events\Identify;

class IdentifyListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public readonly AnalyticsRepositoryContract $repository) {}

    public function handle(Identify $identify): void
    {
        Log::debug('IdentifyListener->handle()', [$identify]);

        // $this->repository->saveGroup(

        // );
    }
}
