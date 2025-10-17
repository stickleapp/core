<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use StickleApp\Core\Contracts\FilterTargetContract;

class Date extends FilterTargetContract
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public Builder $builder,
        public string $attribute
    ) {}

    public function property(): ?string
    {
        return "data->>'{$this->attribute}'";
    }

    #[Override]
    public function castProperty(): mixed
    {
        return sprintf('%s::date', $this->property());
    }
}
