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
    public static function scopeCascade(Builder $builder, Filter $filter)
    {
        return $filter->apply($builder, 'and');
    }

    public static function scopeOrCascade(Builder $builder, Filter $filter)
    {
        return $filter->apply($builder, 'or');
    }

    public static function bootTrackable()
    {

        static::created(function (Model $model) {

            $propertiesToLog = array_intersect($model->getObservableProperties(), array_keys($model->getDirty()));

            foreach ($propertiesToLog as $property) {

                if (is_numeric($model->getAttribute($property))) {

                    ObjectAttribute::create([
                        'object_uid' => $model->object_uid,
                        'object_type' => $model::class,
                        'property' => $property,
                        'old_value' => $model->getOriginal($property),
                        'new_value' => $model->getAttribute($property),
                    ]);
                }
            }
        });

        static::updated(function (Model $model) {

            $propertiesToLog = array_intersect($model->getObservableProperties(), array_keys($model->getDirty()));

            foreach ($propertiesToLog as $property) {

                if (is_numeric($model->getAttribute($property))) {

                    ObjectAttribute::create([
                        'object_uid' => $model->object_uid,
                        'object_type' => $model::class,
                        'property' => $property,
                        'old_value' => $model->getOriginal($property),
                        'new_value' => $model->getAttribute($property),
                    ]);
                }
            }
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
