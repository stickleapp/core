<?php

declare(strict_types=1);

namespace StickleApp\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Asset extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'stickle.asset';
    }
}
