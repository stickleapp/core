<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Jobs\RecordSegmentStatistic as RecordSegmentStatisticJob;
use StickleApp\Core\Models\Segment;

final class RecordSegmentStatistics extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'stickle:record-segment-statitics {segmentId? : A specific segment to export}
                                                            {limit? : The maximum number of segments to export.}';

    /**
     * @var string
     */
    protected $description = 'Store point-in-time values for each segment including value, sum, count, min, max and avg.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] public ?string $prefix = null,
    ) {
        $this->prefix = config('stickle.database.tablePrefix');
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        Log::info('RecordSegmentStatistics Command', $this->arguments());

        $segmentId = $this->argument('segmentId');
        $limit = $this->argument('limit') ?? 25;

        $segments = Segment::all();

        $statistics = $this->getAttributesToRecord($segments);

        $tempTableSql = 'CREATE TEMP TABLE temp_attributes (model VARCHAR(255), attribute VARCHAR(255));';

        DB::statement($tempTableSql);

        DB::table('temp_attributes')->insert(array_values($statistics));

        $rows = DB::table('temp_attributes')
            ->join("{$this->prefix}segments", "{$this->prefix}segments.model", '=', 'temp_attributes.model')
            ->leftJoinSub(
                DB::table("{$this->prefix}segment_statistics")
                    ->select(['segment_id', 'attribute', DB::raw('MAX(recorded_at) as recorded_at')])
                    ->groupBy('segment_id', 'attribute')
                    ->orderBy('recorded_at', 'desc'),
                'exports',
                function ($join) {
                    $join->on('temp_attributes.attribute', '=', 'exports.attribute');
                    $join->on("{$this->prefix}segments.id", '=', 'exports.segment_id');
                }
            )
            ->when($segmentId, function ($query) use ($segmentId) {
                return $query->where("{$this->prefix}segments.id", $segmentId);
            })
            ->select([
                'temp_attributes.model',
                'temp_attributes.attribute',
                "{$this->prefix}segments.id as segment_id",
                'recorded_at',
            ])
            ->orderByRaw('recorded_at asc NULLS FIRST')
            ->limit($limit)
            ->get();

        foreach ($rows as $row) {
            RecordSegmentStatisticJob::dispatch(
                segmentId: $row->segment_id,
                model: $row->model,
                attribute: $row->attribute,
            );
        }
    }

    private function getAttributesToRecord($segments)
    {
        $return = [];
        foreach ($segments as $segment) {
            $model = $segment->model;
            if (in_array($model, $return)) {
                continue;
            }
            $observedAttributes = (new $model)->getObservedAttributes();
            foreach ($observedAttributes as $attribute) {
                $return[md5($model.$attribute)] = [
                    'model' => $model,
                    'attribute' => $attribute,
                ];
            }
        }

        return $return;
    }
}
