<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class FilterTestContract
{
    /**
     * @param  Builder<Model>  $builder
     * @return Builder<Model>
     */
    public function applyFilter(Builder $builder, FilterTargetContract $target, string $operator): Builder
    {
        throw new \Exception('Method applyFilter must be implemented');
    }
}
