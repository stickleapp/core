<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ObjectAttributeChanged;
use StickleApp\Core\Traits\StickleBroadcastsPrivate;

class UserUserRatingChangedListener implements ShouldQueue
{
    public function handle(ObjectAttributeChanged $event): void
    {
        Log::debug('UserUserRatingChangedListener', [$event]);
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
