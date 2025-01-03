<?php

namespace Workbench\App\Listeners;

use Dclaysmith\LaravelCascade\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DoSomethingCoolListener implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {

        Log::debug('DoSomethingCool Handled ObjectEnteredSegment', [$event]);

        // $this->repository->saveGroup(

        // );
    }
}
