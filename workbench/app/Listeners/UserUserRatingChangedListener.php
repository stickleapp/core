<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ObjectAttributeChanged;

class UserUserRatingChangedListener implements ShouldQueue
{
    public function handle(ObjectAttributeChanged $event): void
    {
        Log::debug('UserUserRatingChangedListener', [$event]);
    }
}
