<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Targets;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Illuminate\Container\Attributes\Config;

class Boolean extends FilterTarget
{
    public function __construct(
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix,
        public string $attribute
    ) {}

    public function property(): ?string
    {
        return "model_attributes->>'{$this->attribute}'";
    }

    public function castProperty(): mixed
    {
        return sprintf('(%s)::boolean', $this->property());
    }
}
