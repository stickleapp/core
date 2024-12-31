<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Actions;

use Dclaysmith\LaravelCascade\Models\SegmentStatistic;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordSegmentStatistic
{
    public function __invoke(
        int $segmentId,
        string $model,
        string $attribute
    ): void {
        Log::info('RecordSegmentStatistics', [$segmentId, $model, $attribute]);

        $builder = $this->builder($segmentId, $model, $attribute);

        // ->toArray() doesn't work with the selectRaws
        $items = $builder->get()->transform(function ($item) {
            return (array) $item;
        });

        SegmentStatistic::upsert(
            $items->toArray(),
            uniqueBy: ['segment_id', 'attribute', 'recorded_at'],
            update: ['value_sum', 'value_min', 'value_max', 'value_count']
        );
    }

    private function builder($segmentId, $model, $attribute): Builder
    {
        return DB::table('lc_object_segment')
            ->select('lc_object_segment.segment_id')
            ->selectRaw('? AS attribute', [$attribute])
            ->selectRaw('SUM((lc_object_attributes.model_attributes->>?)::float) AS value_sum', [$attribute])
            ->selectRaw('MIN((lc_object_attributes.model_attributes->>?)::float) AS value_min', [$attribute])
            ->selectRaw('MAX((lc_object_attributes.model_attributes->>?)::float) AS value_max', [$attribute])
            ->selectRaw('COUNT(lc_object_attributes.model_attributes->>?) AS value_count', [$attribute])
            ->selectRAW('CURRENT_DATE AS recorded_at')
            ->join('lc_segments', 'lc_object_segment.segment_id', '=', 'lc_segments.id')
            ->join('lc_object_attributes', function ($join) use ($model) {
                $join->on('lc_object_attributes.object_uid', '=', 'lc_object_segment.object_uid');
                $join->where('lc_object_attributes.model', '=', $model);
            })
            ->groupBy('lc_object_segment.segment_id', 'attribute');
    }
}
