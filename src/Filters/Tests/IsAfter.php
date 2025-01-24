<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use DateTimeInterface;
use StickleApp\\Core\Core\Contracts\FilterTarget;
use StickleApp\\Core\Core\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Number;

class IsAfter extends FilterTest
{
    public function __construct(public DateTimeInterface|string|Number|int $comparator) {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(DB::raw($target->castProperty()), '>', $this->comparator, $operator);
    }
}
