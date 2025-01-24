<?php

namespace StickleApp\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \StickleApp\Core\Laravelstickle
 */
class Laravelstickle extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \StickleApp\Core\Laravelstickle::class;
    }
}
