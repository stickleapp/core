<?php

declare(strict_types=1);

namespace StickleApp\Core\Facades;

use Illuminate\Support\Facades\Facade;
use StickleApp\Core\Support\Asset;

class Asset extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Asset::class;
    }

    // This tells the facade to cache the resolved instance
    protected static function cached(): bool
    {
        return true; // Laravel 11+ only
    }
}
