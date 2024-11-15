<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Targets;

use DateTimeInterface;
use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Filters\Targets\Traits\HasDeltaFilters;
use Illuminate\Database\Eloquent\Builder;

class EventCount extends FilterTarget
{
    use HasDeltaFilters;

    private DateTimeInterface $startDate;

    private DateTimeInterface $endDate;

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

    public function startDate(DateTimeInterface $date): void
    {
        $this->startDate = $date;
    }

    public function endDate(DateTimeInterface $date): void
    {
        $this->endDate = $date;
    }

    public function between(DateTimeInterface $startDate, DateTimeInterface $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}
