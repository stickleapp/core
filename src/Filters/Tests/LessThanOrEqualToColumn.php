<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class LessThanOrEqualToColumn extends FilterTestContract
{
    public function __construct(public string $comparator) {}

    public function applyFilter(Builder $builder, FilterTargetContract $target, string $operator): Builder
    {
        return $builder->whereColumn(DB::raw($target->castProperty()), '<=', $this->comparator);
    }
}
