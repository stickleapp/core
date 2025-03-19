<?php

namespace StickleApp\Core\Filters\Targets\Traits;

use DateTimeInterface;
use StickleApp\Core\Contracts\FilterTargetContract;

trait HasDeltaFilters
{
    /**
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function increased(?array $currentPeriod, ?array $previousPeriod): FilterTargetContract
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            /** @var FilterTargetContract $filterTarget */
            $filterTarget = new $newClass(
                config('stickle.database.tablePrefix'),
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
    public function decreased(?array $currentPeriod, ?array $previousPeriod): FilterTargetContract
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            /** @var FilterTargetContract $filterTarget */
            $filterTarget = new $newClass(
                config('stickle.database.tablePrefix'),
                $this->event,
                $currentPeriod,
                $previousPeriod
            );

            return $filterTarget;
        }

        return $this;
    }
}
