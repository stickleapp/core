<?php

namespace Dclaysmith\LaravelCascade\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dclaysmith\LaravelCascade\LaravelCascade
 */
class LaravelCascade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Dclaysmith\LaravelCascade\LaravelCascade::class;
    }
}
