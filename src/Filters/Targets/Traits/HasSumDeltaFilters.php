<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets\Traits;

use DateTimeInterface;
use StickleApp\Core\Contracts\FilterTargetContract;

trait HasSumDeltaFilters
{
    /**
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function increased(?array $currentPeriod, ?array $previousPeriod): FilterTargetContract
    {
        if (class_basename($this) != class_basename($this).'Delta') {

            $newClass = get_class($this).'Delta';

            /** @var FilterTargetContract $filterTarget */
            $filterTarget = new $newClass(
                config('stickle.database.tablePrefix'),
                $this->property(),
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
        if (class_basename($this) != class_basename($this).'Delta') {

            $newClass = get_class($this).'Delta';

            /** @var FilterTargetContract $filterTarget */
            $filterTarget = new $newClass(
                config('stickle.database.tablePrefix'),
                $this->property(),
                $currentPeriod,
                $previousPeriod
            );

            return $filterTarget;
        }

        return $this;
    }
}
