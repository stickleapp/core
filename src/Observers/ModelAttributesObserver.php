<?php

declare(strict_types=1);

namespace StickleApp\Core\Observers;

use Illuminate\Support\Arr;
use StickleApp\Core\Events\ModelAttributeChanged;
use StickleApp\Core\Models\ModelAttributeAudit;
use StickleApp\Core\Models\ModelAttributes;

class ModelAttributesObserver
{
    /**
     * Handle the ModelAttributes "saved" event.
     */
    public function saved(ModelAttributes $modelAttribute): void
    {

        $from = $modelAttribute->getOriginal('data');
        $to = $modelAttribute->getAttribute('data');

        $diff = $this->getChangedAttributes($from, $to);

        foreach ($diff as $property => $changes) {
            // This may be slow
            ModelAttributeAudit::firstOrCreate([
                'model' => $modelAttribute->model,
                'object_uid' => $modelAttribute->object_uid,
                'attribute' => $property,
                'timestamp' => now(),
            ], [
                'value_old' => Arr::get($changes, 'value_old'),
                'value_new' => Arr::get($changes, 'value_new'),
            ]);
            ModelAttributeChanged::dispatch(
                $modelAttribute->model,
                $modelAttribute->object_uid,
                $property,
                Arr::get($changes, 'value_old'),
                Arr::get($changes, 'value_new')
            );
        }
    }

    /**
     * @param  array<string, string|null>  $original
     * @param  array<string, string|null>  $modified
     * @return array<string, array{value_old: string|null, value_new: string|null}>
     */
    public function getChangedAttributes(?array $original = [], ?array $modified = []): array
    {

        $original = $original ?? [];
        $modified = $modified ?? [];
        $changed = [];

        foreach ($original as $key => $value) {
            // Check if the key exists in the modified array and if the value is different
            if (array_key_exists($key, $modified) && $modified[$key] !== $value) {
                $changed[$key] = [
                    'value_old' => $value,
                    'value_new' => $modified[$key],
                ];
            }
        }

        // Optionally, check for new keys added in the modified array
        foreach ($modified as $key => $value) {
            if (! array_key_exists($key, $original)) {
                $changed[$key] = [
                    'value_old' => null,
                    'value_new' => $value,
                ];
            }
        }

        return $changed;
    }
}
