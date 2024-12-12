<?php

namespace Dclaysmith\LaravelCascade\Filters\Targets\Traits;

use DateTimeInterface;
use Dclaysmith\LaravelCascade\Contracts\FilterTarget;

trait HasDeltaFilters
{
    /**
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function increased(?array $currentPeriod, ?array $previousPeriod): FilterTarget
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            /** @var FilterTarget $filterTarget */
            $filterTarget = new $newClass(
                config('cascade.database.tablePrefix'),
                $this->event,
                $currentPeriod,
                $previousPeriod
            );

            return $filterTarget;
        }

        return $this;
    }

    /**
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function decreased(?array $currentPeriod, ?array $previousPeriod): FilterTarget
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            /** @var FilterTarget $filterTarget */
            $filterTarget = new $newClass(
                config('cascade.database.tablePrefix'),
                $this->event,
                $currentPeriod,
                $previousPeriod
            );

            return $filterTarget;
        }

        return $this;
    }
}
