<?php

namespace Dclaysmith\LaravelCascade\Filters\Targets\Traits;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;

trait HasDeltaFilters
{
    public function increased(?array $range = null): FilterTarget
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            return new $newClass(
                config('cascade.database.tablePrefix'),
                $this->event,
                $range
            );
        }

        return $this;
    }

    public function decreased(?array $range = null): FilterTarget
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            return new $newClass(
                config('cascade.database.tablePrefix'),
                $this->event,
                $range
            );
        }

        return $this;
    }
}
