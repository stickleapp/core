<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use StickleApp\\Core\Core\Contracts\FilterTarget;
use StickleApp\\Core\Core\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LessThanOrEqualTo extends FilterTest
{
    public function __construct(public mixed $comparator) {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(DB::raw($target->castProperty()), '<=', $target->castValue($this->comparator), $operator);
    }
}
