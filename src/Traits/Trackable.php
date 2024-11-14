<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Traits;

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;

trait Trackable
{
    public static function scopeCascade(Builder $builder, Filter $filter)
    {
        return $filter->apply($builder, 'and');
    }

    public static function scopeOrCascade(Builder $builder, Filter $filter)
    {
        return $filter->apply($builder, 'or');
    }
}
