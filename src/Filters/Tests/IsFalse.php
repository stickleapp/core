<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class IsFalse extends FilterTestContract
{
    public function applyFilter(Builder $builder, FilterTargetContract $filterTargetContract, string $operator): Builder
    {
        return $builder->where(DB::raw($filterTargetContract->castProperty()), '=', false, $operator);
    }
}
