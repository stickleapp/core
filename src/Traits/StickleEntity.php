<?php

declare(strict_types=1);

namespace StickleApp\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\ObjectAttribute;
use StickleApp\Core\Models\ObjectAttributesAudit;

trait StickleEntity
{
    public static function getTableName()
    {
        return (new self)->getTable();
    }

    /**
     * Enables a ->stickle() method on the model
     */
    public static function scopeStickle(Builder $builder, Filter $filter)
    {

        $prefix = Config::string('stickle.database.tablePrefix');

        /**
         * We'll need this join for the filters but do not want to add it twice
         */
        if (! $builder->hasJoin("{$prefix}object_attributes")) {
            $builder->leftJoin("{$prefix}object_attributes", function ($join) use ($prefix) {
                $join->on("{$prefix}object_attributes.object_uid", '=', DB::raw(self::getTableName().'.id::text'));
                $join->where("{$prefix}object_attributes.model", '=', self::class);
            });
        }

        return $filter->apply($builder, 'and');
    }

    /**
     * Enables a ->orstickle() method on the model
     */
    public static function scopeOrStickle(Builder $builder, Filter $filter)
    {
        $prefix = Config::string('stickle.database.tablePrefix');

        /**
         * We'll need this join for the filters but do not want to add it twice
         */
        if (! $builder->hasJoin("{$prefix}object_attributes")) {
            $builder->leftJoin("{$prefix}object_attributes", function ($join) use ($prefix) {
                $join->on("{$prefix}object_attributes.object_uid", '=', DB::raw(self::getTableName().'.id::text'));
                $join->where("{$prefix}object_attributes.model", '=', self::class);
            });
        }

        return $filter->apply($builder, 'or');
    }

    public static function bootStickleEntity()
    {

        /**
         * Used when building queries to prevent duplicate joins
         */
        Builder::macro('hasJoin', function ($table, $alias = null) {
            return collect($this->getQuery()->joins)->contains(function ($join) use ($table, $alias) {
                if ($join->table instanceof \Illuminate\Database\Query\Expression) {
                    return $join->table->getValue($join->getGrammar()) === "({$table}) as \"{$alias}\"";
                } else {
                    return $join->table === $table;
                }
            });
        });

        /**
         * When a trackable model is created, log observed attributes
         */
        static::created(function (Model $model) {

            $observableAttributeKeys = $model->getObservedAttributes();

            $model->trackable_attributes = $model->only($observableAttributeKeys);
        });

        /**
         * When a trackable model is updated, log observed attributes
         */
        static::updated(function (Model $model) {

            $observableAttributeKeys = array_intersect($model->getObservedAttributes(), array_keys($model->getDirty()));

            $model->trackable_attributes = $model->only($observableAttributeKeys);
        });

    }

    public function getObservedAttributes()
    {
        return $this->observedAttributes ?? [];
    }

    /**
     * Gets the one-to-one ObjectAttribute relationship
     * ... applies to the models with this trait
     */
    // public function objectAttribute(): MorphOne
    // {
    //     return $this->morphOne(static::class, 'attributable');
    // }
    public function objectAttribute(): HasOne
    {
        return $this->hasOne(ObjectAttribute::class, 'object_uid')->where('model', self::class);
    }

    public function objectAttributesAudits(): HasMany
    {
        return $this->hasMany(ObjectAttributesAudit::class, 'object_uid')->where('model', self::class);
    }

    /**
     * Mutator that allows you to set:
     * `$trackedModel->trackable_attributes = ['key' => 'value', 'key2' => 'value2']`
     *
     * It will retrieve or create the one-to-one relationship with the ObjectAttribute model
     * and merge the provided attributes with the existing ones and persist it to the database
     */
    protected function trackableAttributes(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->objectAttribute()
                    ->firstOrNew(
                        [
                            'model' => self::class,
                            'object_uid' => $this->id,
                        ]
                    )->model_attributes ?? [];
            },
            set: function ($value) {
                if (is_array($value)) {
                    $objectAttribute = $this
                        ->objectAttribute()
                        ->firstOrCreate(
                            [
                                'model' => self::class,
                                'object_uid' => $this->id,
                            ],
                            [
                                'model_attributes' => [],
                            ]
                        );
                    $existingAttributes = $objectAttribute->model_attributes ?? [];
                    $objectAttribute->update(
                        [
                            'model_attributes' => array_merge($existingAttributes, $value),
                            'synced_at' => now(),
                        ]
                    );
                }
            }
        );
    }
}
