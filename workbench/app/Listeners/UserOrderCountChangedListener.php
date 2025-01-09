<?php

namespace Workbench\App\Listeners;

use Dclaysmith\LaravelCascade\Events\ObjectAttributeChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UserOrderCountChangedListener implements ShouldQueue
{
    public function handle(ObjectAttributeChanged $event): void
    {
        Log::debug('UserOrderCountChangedListener', [$event]);
    }
}
