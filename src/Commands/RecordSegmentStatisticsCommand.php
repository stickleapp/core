<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Jobs\RecordSegmentStatisticJob;
use StickleApp\Core\Models\Segment;

final class RecordSegmentStatisticsCommand extends Command implements Isolatable
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
        #[ConfigAttribute('stickle.database.tablePrefix')] public ?string $prefix = null,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info(self::class, $this->arguments());

        $segmentId = $this->argument('segmentId');

        $limit = $this->argument('limit') ?? 10;

        $segments = Segment::all();

        $statistics = $this->getAttributesToRecord($segments);

        $tempTableSql = 'CREATE TEMP TABLE temp_attributes (model_class VARCHAR(255), attribute VARCHAR(255));';

        DB::statement($tempTableSql);

        DB::table('temp_attributes')->insert(array_values($statistics));

        $rows = DB::table('temp_attributes')
            ->join("{$this->prefix}segments", "{$this->prefix}segments.model_class", '=', 'temp_attributes.model')
            ->leftJoin("{$this->prefix}segment_statistic_exports", function ($query) {
                $query->on("{$this->prefix}segment_statistic_exports.segment_id", '=', "{$this->prefix}segments.id");
                $query->on("{$this->prefix}segment_statistic_exports.attribute", '=', 'temp_attributes.attribute');
            })
            ->when($segmentId, function ($query) use ($segmentId) {
                return $query->where("{$this->prefix}segments.id", $segmentId);
            })
            ->select([
                'temp_attributes.model_class',
                'temp_attributes.attribute',
                "{$this->prefix}segments.id as segment_id",
                'last_recorded_at',
            ])
            ->orderByRaw('last_recorded_at asc NULLS FIRST')
            ->limit((int) $limit)
            ->get();

        foreach ($rows as $row) {
            RecordSegmentStatisticJob::dispatch(
                $row->segment_id,
                $row->attribute,
            );
        }
    }

    /**
     * Get the attributes to record for each segment
     *
     * @param  Collection<int, Segment>  $segments
     * @return array<string, array{model_class: string, attribute: string}>
     */
    private function getAttributesToRecord(Collection $segments): array
    {
        $return = [];
        foreach ($segments as $segment) {

            $modelClass = $segment->model_class;
            if (in_array($modelClass, $return)) {
                continue;
            }

            $stickleTrackedAttributes = $modelClass::getStickleObservedAttributes();
            $stickleTrackedAttributes[] = 'count';
            foreach ($stickleTrackedAttributes as $attribute) {
                $return[md5($modelClass.$attribute)] = [
                    'model_class' => $modelClass,
                    'attribute' => $attribute,
                ];
            }
        }

        return $return;
    }
}
