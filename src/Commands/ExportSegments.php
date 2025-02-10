<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use StickleApp\Core\Jobs\ExportSegment;
use StickleApp\Core\Models\Segment;

final class ExportSegments extends Command implements Isolatable
{
    protected string $prefix;

    /**
     * @var string
     */
    protected $signature = 'stickle:export-segments {directory : The full path where the Segment classes are stored.}
                                                    {namespace : The namespace of the Segment classes.}
                                                    {limit : The maximum number of segments to export.}';

    /**
     * @var string
     */
    protected $description = 'Look at the segments in the designated directory, if necessary, export them.';

    /**
     * Create a new command instance.
     */
    public function __construct(
    ) {

        $this->prefix = config('stickle.database.tablePrefix') ?? '';

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        Log::info('ExportSegments Command', $this->arguments());

        $directory = $this->argument('directory');
        $namespace = $this->argument('namespace');

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
        $segments = Segment::where("{$this->prefix}segments.last_exported_at", null)
            ->orWhere("{$this->prefix}segments.last_exported_at", '<', DB::raw("NOW() - INTERVAL '1 minute' * export_interval"))
            ->limit((int) $this->argument('limit'))
            ->get();

        foreach ($segments as $segment) {
            Log::info('ExportSegments Dispatching', ['segment_id' => $segment->id]);
            ExportSegment::dispatch($segment);
            $segment->update(['last_exported_at' => now()]);
        }
    }

    /** @return array<string, mixed> */
    public function getSegmentSyncDefinitions(string $directory, string $namespace): array
    {
        $results = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                /**
                 * @var class-string $className
                 */
                $className = $namespace.'\\'.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    substr($file->getRealPath(), strlen($directory) + 1)
                );

                $reflection = new ReflectionClass($className);
                $defaultProperties = $reflection->getDefaultProperties();

                $results[] = [
                    'model' => Arr::get($defaultProperties, 'model'),
                    'as_class' => $className,
                    'as_json' => null,
                    'export_interval' => config('stickle.schedule.exportSegments'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $results;
    }
}
