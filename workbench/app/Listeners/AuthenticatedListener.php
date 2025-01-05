<?php

namespace Workbench\App\Listeners;

use Dclaysmith\LaravelCascade\Events\ObjectEnteredSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AuthenticatedListener implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        Log::debug('SendLowHealthEmail Handled ObjectEnteredSegment', [$event]);
    }
}
