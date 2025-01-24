<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use StickleApp\\Core\Core\Contracts\FilterTarget;
use Illuminate\Container\Attributes\Config;

class Number extends FilterTarget
{
    public function __construct(
        #[Config('STICKLE.database.tablePrefix')] protected ?string $prefix,
        public string $attribute
    ) {}

    public function property(): ?string
    {
        return "model_attributes->>'{$this->attribute}'";
    }

    public function castProperty(): mixed
    {
        return sprintf('(%s)::numeric', $this->property());
    }
}
