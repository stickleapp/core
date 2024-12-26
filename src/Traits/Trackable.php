<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Traits;

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Models\ObjectAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait Trackable
{
    /**
     * Enables a ->cascade() method on the model
     */
    public static function scopeCascade(Builder $builder, Filter $filter)
    {
        return $filter->apply($builder, 'and');
    }

    /**
     * Enables a ->orCascade() method on the model
     */
    public static function scopeOrCascade(Builder $builder, Filter $filter)
    {
        return $filter->apply($builder, 'or');
    }

    public static function bootTrackable()
    {

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

    // public function setTrackableAttributesProperty($value)
    // {
    //     if (is_array($value)) {

    //         $objectAttribute = $this->attributes()->firstOrNew(['model' => self::class,
    //             'object_uid' => $this->id, ]); // Create a new profile if it doesn't exist

    //         $existingAttributes = $objectAttribute->model_attributes ?? [];

    //         $objectAttribute->update(['model_attributes' => array_merge($existingAttributes, $value)]);
    //     }
    // }
}
