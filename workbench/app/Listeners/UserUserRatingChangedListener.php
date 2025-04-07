<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ModelAttributesChanged;

class UserUserRatingChangedListener implements ShouldQueue
{
    public function handle(ModelAttributesChanged $event): void
    {
        Log::debug('UserUserRatingChangedListener', [$event]);
    }
}
