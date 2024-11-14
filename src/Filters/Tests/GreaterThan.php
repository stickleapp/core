<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Tests;

use DateTimeInterface;
use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;

class GreaterThan extends FilterTest
{
    private $startDate;

    private $endDate;

    public function __construct(public mixed $value) {}

    public function startDate(DateTimeInterface $date): void
    {
        $this->startDate = $date;
    }

    public function endDate(DateTimeInterface $date): void
    {
        $this->endDate = $date;
    }

    public function betweemn(DateTimeInterface $startDate, DateTimeInterface $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function __applyFilter(Builder $builder, FilterTarget $target, $operator): Builder
    {
        return $builder->where($target->__castProperty(), '<', $target->__castValue($this->value), $operator);
    }
}
