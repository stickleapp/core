<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Tests;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IsNotNull extends FilterTest
{
    public function __construct() {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->whereNotNull(DB::raw($target->castProperty()), $operator);
    }
}
