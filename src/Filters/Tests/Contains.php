<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Tests;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Contains extends FilterTest
{
    public function __construct(public string $comparator, public bool $caseSensitive = false) {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->whereLike(DB::raw($target->property()), sprintf('%%%s%%', $this->comparator), $this->caseSensitive);
    }
}
