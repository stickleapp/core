<?php

namespace StickleApp\Core\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Contracts\AnalyticsRepository;
use StickleApp\Core\Events\Group;

class GroupListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public AnalyticsRepository $repository) {}

    public function handle(Group $event): void
    {
        Log::debug('GroupListener->handle()', [$event]);

        // $this->repository->saveGroup(

        // );
    }
}
