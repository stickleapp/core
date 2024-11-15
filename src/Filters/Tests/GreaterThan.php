<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Tests;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;

class GreaterThan extends FilterTest
{
    public function __construct(public mixed $value) {}

    public function __applyFilter(Builder $builder, FilterTarget $target, $operator): Builder
    {
        return $builder->where($target->__castProperty(), '<', $target->__castValue($this->value), $operator);
    }
}
