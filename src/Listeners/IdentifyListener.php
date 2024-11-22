<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Identify;
use Illuminate\Contracts\Queue\ShouldQueue;

class IdentifyListener implements ShouldQueue
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

    public function handle(Identify $event)
    {
        // $this->repository->saveGroup(

        // );
    }
}
