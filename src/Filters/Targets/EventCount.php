<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Targets;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Illuminate\Database\Eloquent\Builder;

class EventCount extends FilterTarget
{
    public function __construct(public string $event) {}

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
