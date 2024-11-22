<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Group;
use Illuminate\Contracts\Queue\ShouldQueue;

class GroupListener implements ShouldQueue
{
    /**
     * @var LaravelCoreRepository
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
        // $this->repository->saveGroup(

        // );
    }
}
