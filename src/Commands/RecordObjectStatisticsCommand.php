<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Jobs\RecordObjectStatisticJob;
use StickleApp\Core\Models\Segment;
use StickleApp\Core\Support\ClassUtils;

final class RecordObjectStatisticsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'stickle:record-object-statitics { model? : A model to export }
                                                            { attribute? : A specific attribute to export }
                                                            { objectUid? : A specific object to export }
                                                            { limit? : The maximum number of objects to export. }';

    /**
     * @var string
     */
    protected $description = 'Store point-in-time agggregate values for object statistics including value, sum, count, min, max and avg.';

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

        $model = $this->argument('model');

        $attribute = $this->argument('attribute');

        $objectUid = $this->argument('objectUid');

        $limit = $this->argument('limit') ?? 10;

        // Get all classes with the StickleEntity trait
        $classes = ClassUtils::getClassesWithTrait(
            config('stickle.namespaces.models'),
            \StickleApp\Core\Traits\StickleEntity::class
        );

        // Filter the classes to only include those that have a relationship with one of these classes
        // Note: Can include relationships with self class

        // Use presense of StickleRelationshipMetadata to decide what
        // we need to be recording...

        // [
        //      [ 'Customer', 'children', [...Customer Tracked Attributes]],
        //      [ 'Customer', 'users', [...User Tracked Attributes] ]
        // ]
        $attributes = [];
        foreach ($classes as $class) {
            if ($relationships = ClassUtils::getRelationshipsWith(app(), $class, [HasMany::class], $classes)) {
                dd($relationships);
                $attributes = $this->getAttributesToRecord($filtered);
            }
        }

        $tempTableSql = 'CREATE TEMP TABLE temp_attributes (model VARCHAR(255), attribute VARCHAR(255));';

        DB::statement($tempTableSql);

        DB::table('temp_attributes')->insert(array_values($statistics));

        $rows = DB::table('temp_attributes')
            ->leftJoin("{$this->prefix}object_statistic_exports", function ($query) {
                $query->on("{$this->prefix}object_statistic_exports.model", '=', 'temp_attributes.model');
                $query->on("{$this->prefix}object_statistic_exports.attribute", '=', 'temp_attributes.attribute');
            })
            // ->when($objectUid, function ($query) use ($objectUid) {
            //     return $query->where("{$this->prefix}segments.id", $objectUid);
            // })
            ->select([
                'temp_attributes.model',
                'temp_attributes.attribute',
                'last_recorded_at',
            ])
            ->orderByRaw('last_recorded_at asc NULLS FIRST')
            ->limit((int) $limit)
            ->get();

        foreach ($rows as $row) {
            RecordObjectStatisticJob::dispatch(
                $row->model,
                $row->attribute,
            );
        }
    }

    /**
     * Get the attributes to record for each segment
     *
     * @param  array<int, string>  $classes
     * @return array<string, array{model: string, attribute: string}>
     */
    private function getAttributesToRecord(array $classes): array
    {
        $return = [];
        foreach ($classes as $class) {
            $stickleTrackedAttributes = $class::getStickleTrackedAttributes();
            foreach ($stickleTrackedAttributes as $attribute) {
                $return[] = [
                    'model' => $class,
                    'attribute' => $attribute,
                ];
            }
        }

        return $return;
    }
}
