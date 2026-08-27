<?php

declare(strict_types=1);

namespace StickleApp\Core\Tests\Fixtures\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use StickleApp\Core\Contracts\FilterTargetContract;

/**
 * Fixture: a target that overrides castValue() observably.
 *
 * The base contract returns the value untouched, so a filter test class that
 * binds its comparator directly looks identical to one that routes it through
 * the target. This fixture makes the difference visible.
 */
class CastingTarget extends FilterTargetContract
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function __construct(Builder $builder, private readonly string $column = 'a_column')
    {
        $this->builder = $builder;
    }

    #[Override]
    public function property(): ?string
    {
        return $this->column;
    }

    #[Override]
    public function castValue(mixed $value): mixed
    {
        return is_string($value) ? 'cast:'.$value : $value;
    }
}
