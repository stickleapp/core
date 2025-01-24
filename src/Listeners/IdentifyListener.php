<?php

namespace StickleApp\Core\Listeners;

use StickleApp\\Core\Core\Contracts\AnalyticsRepository;
use StickleApp\\Core\Core\Events\Identify;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class IdentifyListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(readonly AnalyticsRepository $repository) {}

    public function handle(Identify $event): void
    {
        Log::debug('IdentifyEvent Handled', [$event]);

        // $this->repository->saveGroup(

        // );
    }
}
