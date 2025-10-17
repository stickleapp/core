<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use StickleApp\Core\Traits\StickleEntity;

final class RecordModelAttributesCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'stickle:record-model-attributes {directory : The full path where the Model classes are stored.}
                                                    {namespace : The namespace of the Model classes.}
                                                    {limit? : The maximum number of models to record.}';

    /**
     * @var string
     */
    protected $description = 'Store a point-in-time version of designated object attributes.';

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

        /** @var string $directory */
        $directory = $this->argument('directory');
        /** @var string $namespace */
        $namespace = $this->argument('namespace');
        $limit = $this->argument('limit') ?? 1000;

        // Get all classes with the StickleEntity trait
        $classes = $this->getClassesWithTrait(StickleEntity::class, $directory, $namespace);

        foreach ($classes as $class) {
            /** @var Model $model */
            $model = new $class;

            $builder = $class::query()->leftJoin(
                "{$this->prefix}model_attributes", function ($join) use ($model): void {
                    $join->on(
                        "{$this->prefix}model_attributes.object_uid",
                        '=',
                        DB::raw($model->getTable().'.'.$model->getKeyName().'::text')
                    );
                    $join->where(
                        "{$this->prefix}model_attributes.model_class",
                        '=',
                        $model::class
                    );
                }
            )->where(function ($query): void {
                $query->where('synced_at', '<', now()->subMinutes(config('stickle.schedule.recordModelAttributes', 360)))
                    ->orWhereNull('synced_at');
            })->limit($limit)->select("{$model->getTable()}.*");

            foreach ($builder->cursor() as $stickleEntity) {
                dispatch(function () use ($stickleEntity): void {
                    $attributes = $stickleEntity->getStickleTrackedAttributes();
                    $stickleEntity->trackable_attributes = $stickleEntity->only($attributes);
                });
            }
        }
    }

    /**
     * @param  class-string  $checkForTrait
     * @return array<int, string>
     */
    private function getClassesWithTrait(string $checkForTrait, string $modelsDirectory, string $modelsNamespace): array
    {
        $results = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelsDirectory));
        $a = [];
        foreach ($files as $file) {

            if ($file->isFile() && $file->getExtension() === 'php') {

                $className = $modelsNamespace.'\\'.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    substr((string) $file->getRealPath(), strlen($modelsDirectory) + 1)
                );
                $a[] = $className;
                $traits = class_uses($className);

                if ($traits && array_key_exists($checkForTrait, $traits)) {
                    $results[] = $className;
                }
            }
        }

        return $results;
    }
}
