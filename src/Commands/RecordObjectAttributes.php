<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Commands;

use Dclaysmith\LaravelCascade\Traits\Trackable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class RecordObjectAttributes extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'cascade:record-object-attributes {directory : The full path where the Model classes are stored.}
                                                    {namespace : The namespace of the Model classes.}
                                                    {limit : The maximum number of models to record.}';

    /**
     * @var string
     */
    protected $description = 'Store a point-in-time version of designated object attributes.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        #[Config('cascade.database.tablePrefix')] public ?string $prefix = null,
    ) {
        $this->prefix = config('cascade.database.tablePrefix');
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $directory = $this->argument('directory');
        $namespace = $this->argument('namespace');

        $classes = $this->getClassesWithTrait(Trackable::class, $directory, $namespace);

        foreach ($classes as $class) {
            $object = new $class;
            $builder = $class::query()->leftJoin(
                "{$this->prefix}object_attributes", function ($join) use ($object) {
                    $join->on(
                        "{$this->prefix}object_attributes.object_uid",
                        '=',
                        \DB::raw($object->getTable().'.'.$object->getKeyName().'::text')
                    );
                    $join->where(
                        "{$this->prefix}object_attributes.model",
                        '=',
                        $object::class
                    );
                }
            )->where(function ($query) {
                $query->where('synced_at', '<', now()->subMinutes(360))
                    ->orWhereNull('synced_at');
            })->limit(1000)->select('users.*');

            foreach ($builder->cursor() as $trackable) {
                dispatch(function () use ($trackable) {
                    $attributes = $trackable->getObservedAttributes();
                    $trackable->trackable_attributes = $trackable->only($attributes);
                });
            }
        }
    }

    private function getClassesWithTrait(string $trait, string $modelsDirectory, string $modelsNamespace): array
    {
        $results = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelsDirectory));

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $modelsNamespace.'\\'.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    substr($file->getRealPath(), strlen($modelsDirectory) + 1)
                );
                $traits = class_uses($className);
                if ($traits && in_array($trait, $traits)) {
                    $results[] = $className;
                }
            }
        }

        return $results;
    }
}
