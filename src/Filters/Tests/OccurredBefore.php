<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTarget;
use StickleApp\Core\Contracts\FilterTest;

class OccurredBefore extends FilterTest
{
    public function __construct(public mixed $comparator)
    {
    }

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(function ($query) use ($target) {
            $query->where(DB::raw($target->castProperty()), '<', $target->castValue($this->comparator));
            $query->where(DB::raw($target->castProperty()), '<', Carbon::now()->toDateTimeString());
        });
    }
}
