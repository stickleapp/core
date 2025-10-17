<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Support\Facades\Date;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class WillOccurAfter extends FilterTestContract
{
    public function __construct(public mixed $comparator) {}

    public function applyFilter(Builder $builder, FilterTargetContract $filterTargetContract, string $operator): Builder
    {
        return $builder->where(function (\Illuminate\Contracts\Database\Query\Builder $builder) use ($filterTargetContract): void {
            $builder->where(DB::raw($filterTargetContract->castProperty()), '>', $filterTargetContract->castValue($this->comparator));
            $builder->where(DB::raw($filterTargetContract->castProperty()), '>', Date::now()->toDateTimeString());
        });
    }
}
