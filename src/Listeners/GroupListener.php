<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GroupListener implements ShouldQueue
{
    /**
     * @var LaravelRepository
     */
    protected $repository;

    /**
     * Create the event listener.
     */
    public function __construct(AnalyticsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(Group $event)
    {

        Log::debug('GroupEvent Handled', [$event]);
        // $this->repository->saveGroup(

        // );
    }
}
