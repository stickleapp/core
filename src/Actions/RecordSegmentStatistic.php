<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Models\SegmentStatistic;

class RecordSegmentStatistic
{
    public function __invoke(
        int $segmentId,
        string $model,
        string $attribute
    ): void {

        Log::info('RecordSegmentStatistics', [$segmentId, $model, $attribute]);

        $builder = $this->builder($segmentId, $model, $attribute);

        /** @var \Illuminate\Support\Collection<int, \stdClass> $items */
        $items = $builder->get();

        /** @var callable $callback */
        $callback = fn ($item) => (array) $item;
        $items = $items->transform($callback);

        SegmentStatistic::upsert(
            $items->toArray(),
            uniqueBy: ['segment_id', 'attribute', 'recorded_at'],
            update: ['value', 'value_sum', 'value_min', 'value_max', 'value_count']
        );
    }

    private function builder(int $segmentId, string $model, string $attribute): Builder
    {
        $prefix = config('stickle.database.tablePrefix');

        /** "count" is a special case */
        if ($attribute === 'count') {
            return DB::table("{$prefix}object_segment")
                ->select("{$prefix}object_segment.segment_id")
                ->selectRaw('? AS attribute', [$attribute])
                ->selectRaw("COUNT({$prefix}object_segment.segment_id) AS value")
                ->selectRaw('CURRENT_DATE AS recorded_at')
                ->groupBy("{$prefix}object_segment.segment_id", 'attribute');
        }

        return DB::table("{$prefix}object_segment")
            ->select("{$prefix}object_segment.segment_id")
            ->selectRaw('? AS attribute', [$attribute])
            ->selectRaw("SUM(({$prefix}object_attributes.model_attributes->>?)::float) AS value_sum", [$attribute])
            ->selectRaw("MIN(({$prefix}object_attributes.model_attributes->>?)::float) AS value_min", [$attribute])
            ->selectRaw("MAX(({$prefix}object_attributes.model_attributes->>?)::float) AS value_max", [$attribute])
            ->selectRaw("COUNT({$prefix}object_attributes.model_attributes->>?) AS value_count", [$attribute])
            ->selectRaw('CURRENT_DATE AS recorded_at')
            ->join("{$prefix}segments", "{$prefix}object_segment.segment_id", '=', "{$prefix}segments.id")
            ->join("{$prefix}object_attributes", function ($join) use ($model, $prefix) {
                $join->on("{$prefix}object_attributes.object_uid", '=', "{$prefix}object_segment.object_uid");
                $join->where("{$prefix}object_attributes.model", '=', $model);
            })
            ->groupBy("{$prefix}object_segment.segment_id", 'attribute');
    }
}
