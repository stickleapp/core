<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;

abstract class FilterTargetContract
{
    /** @var Builder<\Illuminate\Database\Eloquent\Model> */
    public Builder $builder;

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

    public function applyJoin(): void {}
}
