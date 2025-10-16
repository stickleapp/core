<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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
    protected $signature = 'stickle:export-segments {namespace : The namespace of the Segment classes.}
                                                    {limit : The maximum number of segments to export.}
                                                    {directory? : The directory where the Segment classes are located.}';

    /**
     * @var string
     */
    protected $description = 'Look at the segments in the designated directory, if necessary, export them.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix = null,
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
        $namespace = $this->argument('namespace');
        /** @var string|null $directory */
        $directory = $this->argument('directory');

        $directory = $directory ?: ClassUtils::directoryFromNamespace(
            namespace: $namespace
        );

        /**
         * Using Reflection, create an array containing all of the segments
         * in the designated directory.
         */
        $segments = $this->getSegmentSyncDefinitions($directory, $namespace);

        /**
         * Insert any segments from this analysis into the `segments` table.
         * If the segment already exists, ignore it.
         */
        Segment::insertOrIgnore($segments);

        /**
         * Return a list of segments to export considering the export_interval
         * and the last_exported_at timestamp.
         */
        $segments = Segment::where(function ($query) {
            $query->where("{$this->prefix}segments.last_exported_at", null);
            $query->orWhere("{$this->prefix}segments.last_exported_at", '<', DB::raw("NOW() - INTERVAL '1 minute' * export_interval"));
        })
            ->limit((int) $this->argument('limit'))
            ->get();

        // ExportSegmentJob::dispatch(Segment::find(1));
        // exit;

        foreach ($segments as $segment) {
            Log::info('ExportSegments Dispatching', ['segment_id' => $segment->id]);
            ExportSegmentJob::dispatch($segment);
            $segment->update(['last_exported_at' => now()]);
        }
    }

    /** @return array<int<0, max>, array<string, mixed>> */
    public function getSegmentSyncDefinitions(string $directory, string $namespace): array
    {
        $results = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($directory)));

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {

                /**
                 * @var class-string $className
                 */
                $className = str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    substr($file->getRealPath(), strlen(realpath($directory)) + 1)
                );

                $segmentClassName = $namespace.'\\'.$className;

                $defaultProperties = ClassUtils::getDefaultAttributesForClass($segmentClassName);

                $model = Arr::get($defaultProperties, 'model');

                $metadata = AttributeUtils::getAttributeForClass(
                    $segmentClassName,
                    StickleSegmentMetadata::class
                );

                $results[] = [
                    'name' => data_get($metadata, 'name'),
                    'description' => data_get($metadata, 'description'),
                    'model_class' => class_basename($model),
                    'as_class' => $className,
                    'as_json' => null,
                    'export_interval' => data_get($metadata, 'exportInterval', config('stickle.schedule.exportSegments')),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $results;
    }
}
