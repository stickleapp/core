<?php

namespace StickleApp\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \StickleApp\Core\Core
 */
class Core extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \StickleApp\Core\Core::class;
    }
}
