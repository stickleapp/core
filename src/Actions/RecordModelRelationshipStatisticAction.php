<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Models\ModelRelationshipStatistic;
use StickleApp\Core\Models\ModelRelationshipStatisticExport;

class RecordModelRelationshipStatisticAction
{
    public function __invoke(
        string $model,
        string $relationship,
        string $related,
        string $attribute
    ): void {

        Log::info(self::class, func_get_args());

        $builder = $this->builder(
            model: $model,
            relationship: $relationship,
            related: $related,
            attribute: $attribute
        );

        /** @var \Illuminate\Support\Collection<int, \stdClass> $items */
        $items = $builder->get();

        ModelRelationshipStatistic::upsert(
            $items->map(function ($model) {
                return (array) $model;
            })->toArray(),
            uniqueBy: ['model', 'object_uid', 'relationship', 'attribute', 'recorded_at'],
            update: ['value', 'value_avg', 'value_sum', 'value_min', 'value_max', 'value_count']
        );

        ModelRelationshipStatisticExport::updateOrCreate(
            [
                'model' => $model,
                'relationship' => $relationship,
                'attribute' => $attribute,
            ],
            [
                'last_recorded_at' => now(),
            ]
        );
    }

    private function builder(
        string $model,
        string $relationship,
        string $related,
        string $attribute
    ): Builder {
        Log::info(self::class, func_get_args());

        $prefix = config('stickle.database.tablePrefix');

        $modelInstance = $model::query()->getModel();

        $relatedInstance = $related::query()->getModel();

        return $model::joinRelationship(
            relation: (new $model)->$relationship(),
            alias: $relationship
        )
            ->join("{$prefix}model_attributes", function ($join) use ($prefix, $relationship, $relatedInstance) {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw('"'.$relationship.'"."'.$relatedInstance->getKeyName().'"::text'));
                $join->where("{$prefix}model_attributes.model", '=', get_class($relatedInstance));
            })
            ->groupBy(
                "{$modelInstance->getTable()}.{$modelInstance->getKeyName()}"
            )
            ->selectRaw(
                "'{$model}' AS model"
            )
            ->selectRaw("{$modelInstance->getTable()}.{$modelInstance->getKeyName()} AS object_uid")
            ->selectRaw(
                "'{$relationship}' AS relationship"
            )
            ->selectRaw(
                "'{$attribute}' AS attribute"
            )
            ->selectRaw(
                "AVG((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_avg",
                [$attribute]
            )
            ->selectRaw(
                "MIN((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_min",
                [$attribute]
            )
            ->selectRaw(
                "MAX((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_max",
                [$attribute]
            )
            ->selectRaw(
                "SUM((jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float) as value_sum",
                [$attribute]
            )
            ->selectRaw(
                'COUNT(*) as value_count'
            )
            ->selectRaw(
                'NOW() as recorded_at'
            )
            ->getQuery();
    }
}
