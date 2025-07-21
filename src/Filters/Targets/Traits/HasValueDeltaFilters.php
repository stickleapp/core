<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets\Traits;

use DateTimeInterface;
use StickleApp\Core\Contracts\FilterTargetContract;

trait HasValueDeltaFilters
{
    /**
     * @param  array<DateTimeInterface>  $period
     */
    public function increased(?array $period): FilterTargetContract
    {
        if (class_basename($this) != class_basename($this).'Delta') {

            $newClass = get_class($this).'Delta';

            /** @var FilterTargetContract $filterTarget */
            $filterTarget = new $newClass(
                config('stickle.database.tablePrefix'),
                $this->property(),
                $period
            );

            return $filterTarget;
        }

        return $this;
    }

    /**
     * @param  array<DateTimeInterface>  $period
     */
    public function decreased(?array $period): FilterTargetContract
    {
        if (class_basename($this) != class_basename($this).'Delta') {

            $newClass = get_class($this).'Delta';

            /** @var FilterTargetContract $filterTarget */
            $filterTarget = new $newClass(
                config('stickle.database.tablePrefix'),
                $this->property(),
                $period
            );

            return $filterTarget;
        }

        return $this;
    }
}
