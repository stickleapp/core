<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Container\Attributes\Config;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Filters\Targets\Traits\HasValueDeltaFilters;

class Number extends FilterTargetContract
{
    use HasValueDeltaFilters;

    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public string $attribute
    ) {}

    public function property(): ?string
    {
        return "data->'{$this->attribute}'";
    }

    public function castProperty(): mixed
    {
        return sprintf('(%s)::numeric', $this->property());
    }
}
