<?php

declare(strict_types=1);

namespace StickleApp\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

final class RecordModelAttributesCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'stickle:record-model-attributes {namespace? : The namespace of the Model classes.}
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

        /** @var string $namespace */
        $namespace = $this->argument('namespace') ?? config('stickle.namespaces.models');
        $limit = $this->argument('limit');

        // Get all classes with the StickleEntity trait
        $classes = ClassUtils::getClassesWithTrait($namespace, StickleEntity::class);

        foreach ($classes as $class) {
            /** @var Model $model */
            $model = new $class;

            $builder = $class::query()
                ->leftJoin(
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
                })->when($limit, fn ($query) => $query->limit($limit))
                ->select("{$model->getTable()}.*");

            foreach ($builder->cursor() as $stickleEntity) {
                dispatch(function () use ($stickleEntity): void {
                    $attributes = $stickleEntity->stickleTrackedAttributes();
                    $stickleEntity->trackable_attributes = $stickleEntity->only($attributes);
                });
            }
        }
    }
}
