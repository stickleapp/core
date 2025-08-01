<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Number;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class IsAfter extends FilterTestContract
{
    public function __construct(public DateTimeInterface|string|Number|int $comparator) {}

    public function applyFilter(Builder $builder, FilterTargetContract $target, string $operator): Builder
    {
        return $builder->where(DB::raw($target->castProperty()), '>', $this->comparator, $operator);
    }
}
