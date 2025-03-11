<?php

namespace Workbench\App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Traits\StickleBroadcasts;

class SendUserLoggedInNotificationListener implements ShouldQueue
{
    use StickleBroadcasts;

    public function handle(Authenticated $event): void
    {
        Log::debug('SendUserLoggedInNotificationListener Handled Authenticated', [$event]);
    }

    public function channels(): string
    {
        return ['Admin'];
    }

    public function with(): array
    {
        return [];
    }
}
