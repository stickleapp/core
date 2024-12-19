<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Traits;

use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Models\ObjectAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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

            $propertiesToLog = $model->getObservableProperties();

            $objectAttributes = ObjectAttribute::firstOrNew([
                'model' => $model::class,
                'object_uid' => $model->id,
            ]);

            $objectAttributes->model_attributes = $model->only($propertiesToLog);

            $objectAttributes->save();
        });

        /**
         * When a trackable model is updated, log observed attributes
         */
        static::updated(function (Model $model) {

            $propertiesToLog = array_intersect($model->getObservableProperties(), array_keys($model->getDirty()));

            $objectAttributes = ObjectAttribute::firstOrNew([
                'model' => $model::class,
                'object_uid' => $model->id,
            ]);

            $attributes = $objectAttributes->model_attributes ?? [];

            $objectAttributes->model_attributes = array_merge($attributes, $model->only($propertiesToLog));

            $objectAttributes->save();
        });

    }

    public function getObservableProperties()
    {
        return $this->observed ?? [];
    }

    /**
     * Get the model's attributes
     */
    public function attributes(): MorphOne
    {
        return $this->morphOne(static::class, 'attributable');
    }
}
