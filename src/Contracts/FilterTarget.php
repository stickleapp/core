<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

use Illuminate\Database\Eloquent\Builder;

abstract class FilterTarget
{
    public function __castValue(mixed $value): mixed
    {
        return $value;
    }

    public function __castProperty(): mixed
    {
        return $this->__property();
    }

    public function __joinKey(): ?string
    {
        return null;
    }

    public function __property(): ?string
    {
        return null;
    }

    public function __applyJoin(Builder $builder): Builder
    {
        return $builder;
    }
}
