<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

use Illuminate\Database\Eloquent\Builder;

abstract class FilterTest
{
    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        throw new \Exception('Method applyFilter must be implemented');
    }
}
