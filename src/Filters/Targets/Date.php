<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Container\Attributes\Config;
use StickleApp\Core\Contracts\FilterTargetContract;

class Date extends FilterTargetContract
{
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public string $attribute
    ) {}

    public function property(): ?string
    {
        return "model_attributes->>'{$this->attribute}'";
    }

    public function castProperty(): mixed
    {
        return sprintf('(%s)::date', $this->property());
    }
}
