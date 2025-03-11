<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\Track;
use StickleApp\Core\Traits\StickleBroadcastsPrivate;

class UpdatedSubscriptionListener implements ShouldQueue
{
    public function handle(Track $event): void
    {
        Log::debug('UpdatedSubscriptionListener Handled Track', [$event]);
    }

    public function channels(): string
    {
        return ['Model.' . $event->model . '.' . $event->objectUid, 'Admin'];
    }

    public function with(): array
    {
        return [];
    }
}
