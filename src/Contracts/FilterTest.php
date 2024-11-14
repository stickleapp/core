<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

use Illuminate\Database\Eloquent\Builder;

abstract class FilterTest
{
    public function __applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        throw new \Exception('Method __applyFilter must be implemented');
    }
}
