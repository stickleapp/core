<?php

namespace Dclaysmith\LaravelCascade\Filters\Targets\Traits;

trait HasDeltaFilters
{
    public function increased(?array $range = null)
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            return new $newClass($this->event, $range);
        }
    }

    public function decreased(?array $range = null)
    {
        if (substr(class_basename($this), -5) === 'Count') {
            $newClass = get_class($this).'Delta';

            return new $newClass($this->event, $range);
        }
    }
}
