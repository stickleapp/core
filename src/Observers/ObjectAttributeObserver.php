<?php

namespace StickleApp\Core\Observers;

use StickleApp\\Core\Core\Events\ObjectAttributeChanged;
use StickleApp\\Core\Core\Models\ObjectAttribute;
use StickleApp\\Core\Core\Models\ObjectAttributesAudit;
use Illuminate\Support\Arr;

class ObjectAttributeObserver
{
    /**
     * Handle the ObjectAttribute "saved" event.
     */
    public function saved(ObjectAttribute $objectAttribute): void
    {

        $from = $objectAttribute->getOriginal('model_attributes');
        $to = $objectAttribute->getAttribute('model_attributes');

        $diff = $this->getChangedAttributes($from, $to);

        foreach ($diff as $property => $changes) {
            // This may be slow
            ObjectAttributesAudit::firstOrCreate([
                'model' => $objectAttribute->model,
                'object_uid' => $objectAttribute->object_uid,
                'attribute' => $property,
                'created_at' => now(),
            ], [
                'from' => Arr::get($changes, 'from'),
                'to' => Arr::get($changes, 'to'),
            ]);

            ObjectAttributeChanged::dispatch(
                $objectAttribute->model,
                $objectAttribute->object_uid,
                $property,
                Arr::get($changes, 'from'),
                Arr::get($changes, 'to')
            );
        }
    }

    public function getChangedAttributes(?array $original = [], ?array $modified = []): array
    {

        $original = $original ?? [];
        $modified = $modified ?? [];
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
