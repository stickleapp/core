<?php

namespace Workbench\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Events\ModelAttributeChanged;

class UserOrderCountChangedListener implements ShouldQueue
{
    public function handle(ModelAttributeChanged $event): void
    {
        Log::debug('UserOrderCountChangedListener', [$event]);
    }
}
