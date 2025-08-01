<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class Between extends FilterTestContract
{
    public function __construct(public mixed $start, public mixed $end) {}

    public function applyFilter(Builder $builder, FilterTargetContract $target, string $operator): Builder
    {
        return $builder->whereBetween(DB::raw($target->castProperty()), [$this->start, $this->end], $operator);
    }
}
