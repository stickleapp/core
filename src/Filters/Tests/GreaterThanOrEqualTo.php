<?php

declare(strict_types=1);

namespace Stickle\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Stickle\Core\Contracts\FilterTarget;
use Stickle\Core\Contracts\FilterTest;

class GreaterThanOrEqualTo extends FilterTest
{
    public function __construct(public mixed $comparator) {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(DB::raw($target->castProperty()), '>=', $target->castValue($this->comparator), $operator);
    }
}
