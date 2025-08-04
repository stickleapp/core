<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class HasBeenInSegment extends FilterTestContract
{
    public function applyFilter(Builder $builder, FilterTargetContract $target, string $operator): Builder
    {
        if ($target->property() === null) {
            throw new \InvalidArgumentException('Filter target property cannot be null');
        }

        return $builder->whereNotNull($target->property());
    }
}
