<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class FilterTarget
{
    public function joinKey(): ?string
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

    /**
     * @return array<string>
     */
    public function definition(): array
    {
        return [];
    }

    /**
     * @param  Builder<Model>  $builder
     * @return Builder<Model> $builder
     */
    public function applyJoin(Builder $builder): Builder
    {
        return $builder;
    }
}
