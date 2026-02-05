<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Jobs\ExportSegmentJob;
use StickleApp\Core\Models\Segment;
use StickleApp\Core\Support\AttributeUtils;
use StickleApp\Core\Support\ClassUtils;

final class ExportSegmentsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'stickle:export-segments {namespace? : The namespace of the Segment classes.}
                                                    {limit? : The maximum number of segments to export.}';

    /**
     * @var string
     */
    protected $description = 'Look at the segments in the designated namespace, if necessary, export them.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')]
        private readonly ?string $prefix = null,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info(self::class, $this->arguments());

        /** @var string $namespace */
        $namespace = $this->argument('namespace') ?? config('stickle.namespaces.segments');
        $limit = $this->argument('limit');

        // Get all segment classes in the namespace
        $segmentClasses = ClassUtils::getClassesInDirectory(
            ClassUtils::directoryFromNamespace($namespace),
            $namespace
        );

        /**
         * Using Reflection, create an array containing all of the segments
         * in the designated directory.
         */
        $segments = $this->getSegmentSyncDefinitions($segmentClasses);

        /**
         * Insert any segments from this analysis into the `segments` table.
         * If the segment already exists, ignore it.
         */
        Segment::query()->insertOrIgnore($segments);

        /**
         * Return a list of segments to export considering the export_interval
         * and the last_exported_at timestamp.
         */
        $segments = Segment::query()->where(function (Builder $builder): void {
            $builder->where("{$this->prefix}segments.last_exported_at");
            $builder->orWhere("{$this->prefix}segments.last_exported_at", '<', DB::raw("NOW() - INTERVAL '1 minute' * export_interval"));
        })
            ->when($limit, fn ($query) => $query->limit((int) $limit))
            ->get();

        foreach ($segments as $segment) {
            Log::info('ExportSegments Dispatching', ['segment_id' => $segment->id]);
            dispatch(new ExportSegmentJob($segment));
            $segment->update(['last_exported_at' => now()]);
        }
    }

    /**
     * @param  array<int, class-string>  $segmentClasses
     * @return array<int<0, max>, array<string, mixed>>
     */
    public function getSegmentSyncDefinitions(array $segmentClasses): array
    {
        $results = [];

        foreach ($segmentClasses as $segmentClass) {
            $defaultProperties = ClassUtils::getDefaultAttributesForClass($segmentClass);

            $model = Arr::get($defaultProperties, 'model');

            $metadata = AttributeUtils::getAttributeForClass(
                $segmentClass,
                StickleSegmentMetadata::class
            );

            $results[] = [
                'name' => data_get($metadata, 'name'),
                'description' => data_get($metadata, 'description'),
                'model_class' => class_basename($model),
                'as_class' => class_basename($segmentClass),
                'as_json' => null,
                'export_interval' => data_get($metadata, 'exportInterval', config('stickle.schedule.exportSegments')),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $results;
    }
}
