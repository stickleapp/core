<?php

namespace Dclaysmith\LaravelCascade\Observers;

use Dclaysmith\LaravelCascade\Models\ObjectAttribute;
use Dclaysmith\LaravelCascade\Models\ObjectAttributesAudit;
use Illuminate\Support\Arr;

class ObjectAttributeObserver
{
    /**
     * Handle the ObjectAttribute "saved" event.
     */
    public function saved(ObjectAttribute $objectAttribute): void
    {

        $from = json_decode($objectAttribute->getOriginal('attributes', '{}'), true);
        $to = json_decode($objectAttribute->getAttribute('attributes', '{}'), true);

        $diff = $this->getChangedAttributes($from, $to);

        foreach ($diff as $property => $changes) {
            ObjectAttributesAudit::create([
                'model' => $objectAttribute->model,
                'object_uid' => $objectAttribute->object_uid,
                'attribute' => $property,
                'from' => Arr::get($changes, 'from'),
                'to' => Arr::get($changes, 'to'),
            ]);
        }
    }

    public function getChangedAttributes(array $original, array $modified)
    {
        $changed = [];

        foreach ($original as $key => $value) {
            // Check if the key exists in the modified array and if the value is different
            if (array_key_exists($key, $modified) && $modified[$key] !== $value) {
                $changed[$key] = [
                    'from' => $value,
                    'to' => $modified[$key],
                ];
            }
        }

        // Optionally, check for new keys added in the modified array
        foreach ($modified as $key => $value) {
            if (! array_key_exists($key, $original)) {
                $changed[$key] = [
                    'from' => null,
                    'to' => $value,
                ];
            }
        }

        return $changed;
    }
}
