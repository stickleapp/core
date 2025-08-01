<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class OccurredBefore extends FilterTestContract
{
    public function __construct(public mixed $comparator) {}

    public function applyFilter(Builder $builder, FilterTargetContract $target, string $operator): Builder
    {
        return $builder->where(function ($query) use ($target) {
            $query->where(DB::raw($target->castProperty()), '<', $target->castValue($this->comparator));
            $query->where(DB::raw($target->castProperty()), '<', Carbon::now()->toDateTimeString());
        });
    }
}
