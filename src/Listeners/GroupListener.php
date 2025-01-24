<?php

namespace StickleApp\Core\Listeners;

use StickleApp\\Core\Core\Contracts\AnalyticsRepository;
use StickleApp\\Core\Core\Events\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GroupListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public AnalyticsRepository $repository) {}

    public function handle(Group $event): void
    {

        Log::debug('GroupEvent Handled', [$event]);

        // $this->repository->saveGroup(

        // );
    }
}
