<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Models\ObjectStatistic;

class RecordObjectStatisticAction
{
    public function __invoke(
        string $model,
        string $attribute
    ): void {

        Log::info(self::class, func_get_args());

        $builder = $this->builder($model, $attribute);

        /** @var \Illuminate\Support\Collection<int, \stdClass> $items */
        $items = $builder->get();
        dd($items);
        ObjectStatistic::upsert(
            $items->map(function ($model) {
                return (array) $model;
            })->toArray(),
            uniqueBy: ['model', 'attribute',  'recorded_at'],
            update: ['value', 'value_avg', 'value_sum', 'value_min', 'value_max', 'value_count']
        );

        SegmentStatisticExport::updateOrCreate(
            [
                'model' => $model,
                'attribute' => $attribute,
            ],
            [
                'last_recorded_at' => now(),
            ]
        );
    }

    private function builder(string $class, string $attribute): Builder
    {
        Log::info(self::class, func_get_args());

        $prefix = config('stickle.database.tablePrefix');

        $model = $class::query()->getModel();

        $builder = $class::query()
            ->join("{$prefix}object_attributes", function ($join) use ($prefix, $model) {
                $join->on("{$prefix}object_attributes.object_uid", '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
                $join->where("{$prefix}object_attributes.model", '=', get_class($model));
            })
            ->selectRaw(
                "AVG((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_avg",
                [$attribute]
            )
            ->selectRaw(
                "MIN((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_min",
                [$attribute]
            )
            ->selectRaw(
                "MAX((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_max",
                [$attribute]
            )
            ->selectRaw(
                "SUM((jsonb_extract_path_text({$prefix}object_attributes.model_attributes, ?))::float) as value_sum",
                [$attribute]
            )
            ->selectRaw(
                'COUNT(*) as value_count'
            );

        dd($builder->get());

        // /** "count" is a special case */
        // if ($attribute === 'count') {
        //     return DB::table("{$prefix}object_segment")
        //         ->where('segment_id', $segmentId)
        //         ->select("{$prefix}object_segment.segment_id")
        //         ->selectRaw('? AS attribute', [$attribute])
        //         ->selectRaw("COUNT({$prefix}object_segment.segment_id) AS value")
        //         ->selectRaw('CURRENT_DATE AS recorded_at')
        //         ->groupBy("{$prefix}object_segment.segment_id", 'attribute');
        // }

        // return DB::table("{$prefix}object_segment")
        //     ->join("{$prefix}segments", "{$prefix}object_segment.segment_id", '=', "{$prefix}segments.id")
        //     ->join("{$prefix}object_attributes", function ($join) use ($prefix) {
        //         $join->on("{$prefix}object_attributes.model", '=', "{$prefix}segments.model");
        //         $join->on("{$prefix}object_attributes.object_uid", '=', "{$prefix}object_segment.object_uid");
        //     })
        //     ->where("{$prefix}object_segment.segment_id", $segmentId)
        //     ->groupBy("{$prefix}object_segment.segment_id", 'attribute')
        //     ->select("{$prefix}object_segment.segment_id")
        //     ->selectRaw('? AS attribute', [$attribute])
        //     ->selectRaw("AVG(({$prefix}object_attributes.model_attributes->>?)::float) AS value_avg", [$attribute])
        //     ->selectRaw("SUM(({$prefix}object_attributes.model_attributes->>?)::float) AS value_sum", [$attribute])
        //     ->selectRaw("MIN(({$prefix}object_attributes.model_attributes->>?)::float) AS value_min", [$attribute])
        //     ->selectRaw("MAX(({$prefix}object_attributes.model_attributes->>?)::float) AS value_max", [$attribute])
        //     ->selectRaw("COUNT({$prefix}object_attributes.model_attributes->>?) AS value_count", [$attribute])
        //     ->selectRaw('CURRENT_DATE AS recorded_at');

        // SELECT
        // 	'Workbench\App\Models\Customer' AS model
        // 	,  customers.parent_id AS object_uid
        // 	,  'ticket_count' AS attribute
        // 	,  COUNT(stc_object_attributes.model_attributes->>'ticket_count') AS count
        // 	,  SUM((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS sum
        // 	,  AVG((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS avg
        // 	,  MIN((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS min
        // 	,  MAX((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS max
        // FROM
        //     stc_object_attributes
        // JOIN
        // 	customers
        // ON
        // 	(customers.id::text = stc_object_attributes.object_uid
        // 		AND
        // 	'Workbench\App\Models\Customer' = stc_object_attributes.model)
        // GROUP BY
        //     model,
        //     customers.parent_id
        // ORDER BY
        //     model,
        //     customers.parent_id

        // SELECT
        // 	'Workbench\App\Models\Customer' AS model
        // 	,  users.customer_id AS object_uid
        // 	,  'users.ticket_count' AS attribute
        // 	,  COUNT(stc_object_attributes.model_attributes->>'ticket_count') AS count
        // 	,  SUM((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS sum
        // 	,  AVG((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS avg
        // 	,  MIN((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS min
        // 	,  MAX((stc_object_attributes.model_attributes->>'ticket_count')::numeric) AS max
        // FROM
        //     stc_object_attributes
        // JOIN
        // 	users
        // ON
        // 	(users.id::text = stc_object_attributes.object_uid
        // 		AND
        // 	'Workbench\App\Models\User' = stc_object_attributes.model)
        // GROUP BY
        //     model,
        //     users.customer_id
        // ORDER BY
        //     model,
        //     users.customer_id

    }
}
