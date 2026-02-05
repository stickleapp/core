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
    public function saved(ModelAttributes $modelAttributes): void
    {

        $from = $modelAttributes->getOriginal('data');
        $to = $modelAttributes->getAttribute('data');

        $diff = $this->getChangedAttributes($from, $to);

        foreach ($diff as $property => $changes) {

            $valueOld = Arr::get($changes, 'value_old');
            $valueNew = Arr::get($changes, 'value_new');

            // This may be slow
            ModelAttributeAudit::query()->firstOrCreate([
                'model_class' => $modelAttributes->model_class,
                'object_uid' => $modelAttributes->object_uid,
                'attribute' => $property,
                'timestamp' => now(),
            ], [
                'value_old' => $valueOld,
                'value_new' => $valueNew,
            ]);

            event(new ModelAttributeChanged(
                $modelAttributes->model_class,
                (string) $modelAttributes->object_uid,
                $property,
                $valueOld !== null ? (string) $valueOld : null,
                $valueNew !== null ? (string) $valueNew : null
            ));
        }
    }

    /**
     * @param  array<string, string|null>  $original
     * @param  array<string, string|null>  $modified
     * @return array<string, array{value_old: string|null, value_new: string|null}>
     */
    public function getChangedAttributes(?array $original = [], ?array $modified = []): array
    {

        $original ??= [];
        $modified ??= [];
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
