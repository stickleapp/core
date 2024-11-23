<?php

namespace Dclaysmith\LaravelCascade\Listeners;

use Dclaysmith\LaravelCascade\Contracts\AnalyticsRepository;
use Dclaysmith\LaravelCascade\Events\Identify;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class IdentifyListener implements ShouldQueue
{
    /**
     * @var AnalyticsRepository
     */
    protected $repository;

    /**
     * Create the event listener.
     */
    public function __construct(AnalyticsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(Identify $event): void
    {
        Log::debug('IdentifyEvent Handled', [$event]);

        // $this->repository->saveGroup(

        // );
    }
}
