<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Tests;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IsNotTrue extends FilterTest
{
    public function __construct() {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(function (Builder $query) use ($target) {
            $query->where(DB::raw($target->castProperty()), false);
            $query->orWhereNull(DB::raw($target->castProperty()));
        });
    }
}
