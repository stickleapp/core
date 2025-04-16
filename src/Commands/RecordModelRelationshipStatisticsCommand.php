<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Jobs\RecordModelRelationshipStatisticJob;
use StickleApp\Core\Support\ClassUtils;

final class RecordModelRelationshipStatisticsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'stickle:record-model-relationship-statitics { limit? : The maximum number of objects to export. }';

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

        $limit = $this->argument('limit') ?? 10;

        // Get all classes with the StickleEntity trait
        $modelClasses = ClassUtils::getClassesWithTrait(
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
        foreach ($modelClasses as $modelClass) {
            $stickleTrackedAttributes = $modelClass::getStickleTrackedAttributes();
            if ($relationships = ClassUtils::getRelationshipsWith(app(), $modelClass, [HasMany::class], $modelClasses)) {
                foreach ($relationships as $relationship) {
                    foreach ($stickleTrackedAttributes as $attribute) {
                        $attributes[] = [
                            'model_class' => class_basename($modelClass),
                            'relationship' => $relationship['name'],
                            'related_class' => $relationship['related'],
                            'attribute' => $attribute,
                        ];
                    }
                }
            }
        }

        $tempTableSql = 'CREATE TEMP TABLE temp_attributes (model_class TEXT, relationship TEXT, related_class TEXT, attribute TEXT);';

        DB::statement($tempTableSql);

        DB::table('temp_attributes')->insert($attributes);

        $rows = DB::table('temp_attributes')
            ->leftJoin("{$this->prefix}model_relationship_statistic_exports", function ($query) {
                $query->on("{$this->prefix}model_relationship_statistic_exports.model_class", '=', 'temp_attributes.model');
                $query->on("{$this->prefix}model_relationship_statistic_exports.relationship", '=', 'temp_attributes.relationship');
                $query->on("{$this->prefix}model_relationship_statistic_exports.attribute", '=', 'temp_attributes.attribute');
            })
            ->select([
                'temp_attributes.model_class',
                'temp_attributes.relationship',
                'temp_attributes.related_class',
                'temp_attributes.attribute',
                'last_recorded_at',
            ])
            ->orderByRaw('last_recorded_at asc NULLS FIRST')
            ->limit((int) $limit)
            ->get();

        foreach ($rows as $row) {
            RecordModelRelationshipStatisticJob::dispatch(
                $row->model_class,
                $row->relationship,
                $row->related_class,
                $row->attribute
            );
        }
    }
}
