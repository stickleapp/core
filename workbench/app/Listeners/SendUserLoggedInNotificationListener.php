<?php

namespace Workbench\App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendUserLoggedInNotificationListener implements ShouldQueue
{
    public function handle(Authenticated $authenticated): void
    {
        Log::debug('SendUserLoggedInNotificationListener Handled Authenticated', [$authenticated]);
    }
}
