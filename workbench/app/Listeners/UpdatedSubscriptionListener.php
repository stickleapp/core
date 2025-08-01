<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\Track;

class UpdatedSubscriptionListener implements ShouldQueue
{
    public function handle(Track $event): void
    {
        Log::debug('UpdatedSubscriptionListener Handled Track', [$event]);
    }
}
