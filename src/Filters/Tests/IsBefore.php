<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Support\Number;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class IsBefore extends FilterTestContract
{
    public function __construct(public DateTimeInterface|string|Number|int $comparator) {}

    public function applyFilter(Builder $builder, FilterTargetContract $filterTargetContract, string $operator): Builder
    {
        return $builder->where(DB::raw($filterTargetContract->castProperty()), '<', $this->comparator, $operator);
    }
}
