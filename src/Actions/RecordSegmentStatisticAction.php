<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Support\Collection;
use stdClass;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Models\SegmentStatistic;
use StickleApp\Core\Models\SegmentStatisticExport;

class RecordSegmentStatisticAction
{
    public function __invoke(
        int $segmentId,
        string $attribute
    ): void {

        Log::info(self::class, func_get_args());

        $builder = $this->builder($segmentId, $attribute);

        /** @var Collection<int, stdClass> $items */
        $items = $builder->get();

        SegmentStatistic::query()->upsert($items->map(fn($model): array => (array) $model)->all(), uniqueBy: ['segment_id', 'attribute', 'recorded_at'], update: ['value', 'value_avg', 'value_sum', 'value_min', 'value_max', 'value_count']);

        SegmentStatisticExport::query()->updateOrCreate([
            'segment_id' => $segmentId,
            'attribute' => $attribute,
        ], [
            'last_recorded_at' => now(),
        ]);
    }

    private function builder(int $segmentId, string $attribute): Builder
    {
        $prefix = config('stickle.database.tablePrefix');

        /** "count" is a special case */
        if ($attribute === 'count') {
            return DB::table("{$prefix}model_segment")
                ->where('segment_id', $segmentId)
                ->select("{$prefix}model_segment.segment_id")
                ->selectRaw('? AS attribute', [$attribute])
                ->selectRaw("COUNT({$prefix}model_segment.segment_id) AS value")
                ->selectRaw('CURRENT_DATE AS recorded_at')
                ->groupBy("{$prefix}model_segment.segment_id", 'attribute');
        }

        return DB::table("{$prefix}model_segment")
            ->join("{$prefix}segments", "{$prefix}model_segment.segment_id", '=', "{$prefix}segments.id")
            ->join("{$prefix}model_attributes", function ($join) use ($prefix): void {
                $join->on("{$prefix}model_attributes.model_class", '=', "{$prefix}segments.model_class");
                $join->on("{$prefix}model_attributes.object_uid", '=', "{$prefix}model_segment.object_uid");
            })
            ->where("{$prefix}model_segment.segment_id", $segmentId)
            ->groupBy("{$prefix}model_segment.segment_id", 'attribute')
            ->select("{$prefix}model_segment.segment_id")
            ->selectRaw('? AS attribute', [$attribute])
            ->selectRaw("AVG(({$prefix}model_attributes.data->>?)::float) AS value_avg", [$attribute])
            ->selectRaw("SUM(({$prefix}model_attributes.data->>?)::float) AS value_sum", [$attribute])
            ->selectRaw("MIN(({$prefix}model_attributes.data->>?)::float) AS value_min", [$attribute])
            ->selectRaw("MAX(({$prefix}model_attributes.data->>?)::float) AS value_max", [$attribute])
            ->selectRaw("COUNT({$prefix}model_attributes.data->>?) AS value_count", [$attribute])
            ->selectRaw('CURRENT_DATE AS recorded_at');
    }
}
