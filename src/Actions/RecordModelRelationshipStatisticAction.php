<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use StickleApp\Core\Models\ModelRelationshipStatistic;
use StickleApp\Core\Models\ModelRelationshipStatisticExport;
use StickleApp\Core\Support\ClassUtils;

class RecordModelRelationshipStatisticAction
{
    public function __invoke(
        string $modelClass,
        string $relationship,
        string $relatedClass,
        string $attribute
    ): void {

        Log::info(self::class, func_get_args());

        $builder = $this->builder(
            modelClass: $modelClass,
            relationship: $relationship,
            relatedClass: $relatedClass,
            attribute: $attribute
        );

        /** @var Collection<int, stdClass> $items */
        $items = $builder->get();

        ModelRelationshipStatistic::query()->upsert($items->map(fn ($item): array => (array) $item)->all(), uniqueBy: ['model_class', 'object_uid', 'relationship', 'attribute', 'recorded_at'], update: ['value', 'value_avg', 'value_sum', 'value_min', 'value_max', 'value_count']);

        ModelRelationshipStatisticExport::query()->updateOrCreate([
            'model_class' => $modelClass,
            'relationship' => $relationship,
            'attribute' => $attribute,
        ], [
            'last_recorded_at' => now(),
        ]);
    }

    private function builder(
        string $modelClass,
        string $relationship,
        string $relatedClass,
        string $attribute
    ): Builder {

        Log::info(self::class, func_get_args());

        $prefix = config('stickle.database.tablePrefix');

        $model = $modelClass::query()->getModel();

        $relatedModel = $relatedClass::query()->getModel();

        return $modelClass::joinRelationship(
            relation: (new $model)->$relationship(),
            alias: $relationship
        )
            ->join("{$prefix}model_attributes", function ($join) use ($prefix, $relationship, $relatedModel): void {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw('"'.$relationship.'"."'.$relatedModel->getKeyName().'"::text'));
                $join->where("{$prefix}model_attributes.model_class", '=', ClassUtils::storeModelClass($relatedModel));
            })
            ->groupBy(
                "{$model->getTable()}.{$model->getKeyName()}"
            )
            /**
             * $model is a Model instance, so interpolating it stringified it
             * through Model::__toString() and wrote its JSON into model_class.
             * Nothing could read those rows back: modelRelationshipStatistics()
             * matches on the stored basename.
             */
            ->selectRaw(
                "'".ClassUtils::storeModelClass($model)."' AS model_class"
            )
            ->selectRaw("{$model->getTable()}.{$model->getKeyName()} AS object_uid")
            ->selectRaw(
                "'{$relationship}' AS relationship"
            )
            ->selectRaw(
                "'{$attribute}' AS attribute"
            )
            ->selectRaw(
                "AVG(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_avg",
                [$attribute, $attribute]
            )
            ->selectRaw(
                "MIN(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_min",
                [$attribute, $attribute]
            )
            ->selectRaw(
                "MAX(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_max",
                [$attribute, $attribute]
            )
            ->selectRaw(
                "SUM(CASE WHEN jsonb_typeof({$prefix}model_attributes.data -> ?) = 'number' THEN (jsonb_extract_path_text({$prefix}model_attributes.data, ?))::float END) as value_sum",
                [$attribute, $attribute]
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
