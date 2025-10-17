<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class FilterTargetContract
{
    /** @var Builder<Model> */
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
