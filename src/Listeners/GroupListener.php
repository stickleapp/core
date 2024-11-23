<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Group;
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
