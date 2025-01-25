<?php

namespace StickleApp\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepository;
use StickleApp\Core\Events\Identify;

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
