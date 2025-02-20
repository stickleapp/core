<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Models\SegmentStatistic;
use StickleApp\Core\Models\SegmentStatisticExport;
use Illuminate\Support\Facades\Config;

class RecordSegmentStatistic
{
    public function __invoke(
        int $segmentId,
        string $attribute
    ): void {

        Log::info('RecordSegmentStatisticAction', [$segmentId, $attribute]);

        $builder = $this->builder($segmentId, $attribute);

        /** @var \Illuminate\Support\Collection<int, \stdClass> $items */
        $items = $builder->get();

        /** @var callable $callback */
        $callback = fn ($item) => (array) $item;
        $items = $items->transform($callback);

        SegmentStatistic::upsert(
            $items->toArray(),
            uniqueBy: ['segment_id', 'attribute', 'recorded_at'],
            update: ['value', 'value_avg', 'value_sum', 'value_min', 'value_max', 'value_count']
        );

        SegmentStatisticExport::updateOrCreate([
            'segment_id' => $segmentId,
            'attribute' => $attribute,
        ],
            [
                'last_recorded_at' => now(),
            ]
        );
    }

    private function builder(int $segmentId, string $attribute): Builder
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        /** "count" is a special case */
        if ($attribute === 'count') {
            return DB::table("{$prefix}object_segment")
                ->where('segment_id', $segmentId)
                ->select("{$prefix}object_segment.segment_id")
                ->selectRaw('? AS attribute', [$attribute])
                ->selectRaw("COUNT({$prefix}object_segment.segment_id) AS value")
                ->selectRaw('CURRENT_DATE AS recorded_at')
                ->groupBy("{$prefix}object_segment.segment_id", 'attribute');
        }

        return DB::table("{$prefix}object_segment")
            ->join("{$prefix}segments", "{$prefix}object_segment.segment_id", '=', "{$prefix}segments.id")
            ->join("{$prefix}object_attributes", function ($join) use ($prefix) {
                $join->on("{$prefix}object_attributes.model", '=', "{$prefix}segments.model");
                $join->on("{$prefix}object_attributes.object_uid", '=', "{$prefix}object_segment.object_uid");
            })
            ->where("{$prefix}object_segment.segment_id", $segmentId)
            ->groupBy("{$prefix}object_segment.segment_id", 'attribute')
            ->select("{$prefix}object_segment.segment_id")
            ->selectRaw('? AS attribute', [$attribute])
            ->selectRaw("AVG(({$prefix}object_attributes.model_attributes->>?)::float) AS value_avg", [$attribute])
            ->selectRaw("SUM(({$prefix}object_attributes.model_attributes->>?)::float) AS value_sum", [$attribute])
            ->selectRaw("MIN(({$prefix}object_attributes.model_attributes->>?)::float) AS value_min", [$attribute])
            ->selectRaw("MAX(({$prefix}object_attributes.model_attributes->>?)::float) AS value_max", [$attribute])
            ->selectRaw("COUNT({$prefix}object_attributes.model_attributes->>?) AS value_count", [$attribute])
            ->selectRaw('CURRENT_DATE AS recorded_at');
    }
}
