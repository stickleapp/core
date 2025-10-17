<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Contracts\FilterTestContract;

class HasBeenInSegment extends FilterTestContract
{
    public function applyFilter(Builder $builder, FilterTargetContract $filterTargetContract, string $operator): Builder
    {
        throw_if($filterTargetContract->property() === null, InvalidArgumentException::class, 'Filter target property cannot be null');

        return $builder->whereNotNull($filterTargetContract->property());
    }
}
