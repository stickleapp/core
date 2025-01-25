<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ObjectEnteredSegment;

class AuthenticatedListener implements ShouldQueue
{
    public function handle(ObjectEnteredSegment $event): void
    {
        Log::debug('SendLowHealthEmail Handled ObjectEnteredSegment', [$event]);
    }
}
