<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Targets;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Filters\Targets\Traits\HasDeltaFilters;
use Illuminate\Database\Eloquent\Builder;

class EventCount extends FilterTarget
{
    use HasDeltaFilters;

    public function __construct(public string $event, public array $dateRange) {}

    public function __property(): ?string
    {
        return $this->event;
    }

    public function __castProperty(): mixed
    {
        return $this->__property();
    }

    public function __applyJoin(Builder $builder): Builder
    {
        /**
         * @todo Implement __applyJoin() method.
         *
         * Join necessary tables (event_counts)
         */
        return $builder;
    }
}
