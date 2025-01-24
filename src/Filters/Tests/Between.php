<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use StickleApp\\Core\Core\Contracts\FilterTarget;
use StickleApp\\Core\Core\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Between extends FilterTest
{
    public function __construct(public mixed $start, public mixed $end) {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->whereBetween(DB::raw($target->castProperty()), [$this->start, $this->end], $operator);
    }
}
