<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTarget;
use StickleApp\Core\Contracts\FilterTest;

class IsNotFalse extends FilterTest
{
    public function __construct() {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(function (Builder $query) use ($target) {
            $query->where(DB::raw($target->castProperty()), '!=', false);
            $query->orWhereNull(DB::raw($target->castProperty()));
        });
    }
}
