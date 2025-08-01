<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ModelEnteredSegment;

class SendLowHealthEmail implements ShouldQueue
{
    public function handle(ModelEnteredSegment $event): void
    {
        Log::debug('SendLowHealthEmail Handled ModelEnteredSegment', [$event]);
    }
}
