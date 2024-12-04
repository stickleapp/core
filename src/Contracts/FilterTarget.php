<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

use Illuminate\Database\Eloquent\Builder;

abstract class FilterTarget
{
    public array $joins = [];

    final public function joinKey(): ?string
    {
        return md5(self::class.''.json_encode(self::definition()));
    }

    public function castValue(mixed $value): mixed
    {
        return $value;
    }

    public function castProperty(): mixed
    {
        return $this->property();
    }

    public function property(): ?string
    {
        return null;
    }

    public function definition(): array
    {
        return [];
    }

    public function applyJoin(Builder $builder): Builder
    {
        return $builder;
    }
}
