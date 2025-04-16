<?php

declare(strict_types=1);

namespace StickleApp\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Attributes\StickleRelationshipMetadata;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\ModelAttributeAudit;
use StickleApp\Core\Models\ModelAttributes;
use StickleApp\Core\Models\ModelRelationshipStatistic;
use StickleApp\Core\Support\AttributeUtils;
use StickleApp\Core\Support\ClassUtils;

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

        $prefix = config('stickle.database.tablePrefix');

        /**
         * We'll need this join for the filters but do not want to add it twice
         */
        if (! $builder->hasJoin("{$prefix}model_attributes")) {
            $builder->leftJoin("{$prefix}model_attributes", function ($join) use ($prefix) {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw(self::getTableName().'.id::text'));
                $join->where("{$prefix}model_attributes.model_class", '=', self::class);
            });
        }

        return $filter->apply($builder, 'and');
    }

    /**
     * Enables a ->orstickle() method on the model
     */
    public static function scopeOrStickle(Builder $builder, Filter $filter)
    {
        $prefix = config('stickle.database.tablePrefix');

        /**
         * We'll need this join for the filters but do not want to add it twice
         */
        if (! $builder->hasJoin("{$prefix}model_attributes")) {
            $builder->leftJoin("{$prefix}model_attributes", function ($join) use ($prefix) {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw(self::getTableName().'.id::text'));
                $join->where("{$prefix}model_attributes.model_class", '=', self::class);
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

        Builder::macro('joinRelationship', function (Relation $relation, string $alias, string $joinType = 'inner') {

            $relatedTable = $relation->getRelated()->getTable();
            $parentTable = $relation->getParent()->getTable();

            // Handle different relationship types
            if ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasOneOrMany) {
                $foreignKey = $relation->getQualifiedForeignKeyName();
                $localKey = $relation->getQualifiedParentKeyName();
            } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                $foreignKey = $relation->getQualifiedForeignKeyName();
                $localKey = $relation->getQualifiedOwnerKeyName();
            } else {
                throw new \Exception('Unsupported relation type: '.get_class($relation));
            }

            // If the related table is aliased, we need to adjust the foreign key
            $foreignKey = str_replace($relatedTable, $alias, $relation->getQualifiedForeignKeyName());

            // Get the basic query constraints (like where clauses) from the relation
            $baseQuery = $relation->getQuery();
            $wheres = $baseQuery->getQuery()->wheres;

            // Apply the join
            $tableToJoin = $alias ? "$relatedTable as $alias" : $relatedTable;
            $joinTable = $alias ? $alias : $relatedTable;

            $this->join($tableToJoin, function ($join) use ($foreignKey, $localKey, $wheres, $joinTable) {
                $join->on($foreignKey, '=', $localKey);

                // Apply any additional constraints from the relationship
                foreach ($wheres as $where) {
                    if ($where['type'] === 'Basic') {
                        // Adjust column name if it doesn't include a table prefix
                        $column = $where['column'];
                        if (strpos($column, '.') === false) {
                            $column = "$joinTable.$column";
                        }

                        $join->where($column, $where['operator'], $where['value'], $where['boolean']);
                    }
                }
            }, null, null, $joinType);

            return $this;
        });

        /**
         * When a trackable model is created, log observed attributes
         */
        static::created(function (Model $model) {

            $observableAttributeKeys = $model->getStickleObservedAttributes();

            $model->trackable_attributes = $model->only($observableAttributeKeys);
        });

        /**
         * When a trackable model is updated, log observed attributes
         */
        static::updated(function (Model $model) {

            $observableAttributeKeys = array_intersect($model::getStickleObservedAttributes(), array_keys($model->getDirty()));

            $model->trackable_attributes = $model->only($observableAttributeKeys);
        });

    }

    /**
     * This attribute is used to define which attributes should be watched for changes
     *
     * It is defined on the parent model
     */
    public static function getStickleObservedAttributes()
    {

        if (property_exists(static::class, 'stickleObservedAttributes') && isset(static::$stickleObservedAttributes)) {
            return static::$stickleObservedAttributes;
        }

        return [];
    }

    public static function getStickleTrackedAttributes()
    {
        if (property_exists(static::class, 'stickleTrackedAttributes') && isset(static::$stickleTrackedAttributes)) {
            return static::$stickleTrackedAttributes;
        }

        return [];
    }

    public function modelAttributes(): HasOne
    {
        return $this->hasOne(ModelAttributes::class, 'object_uid')->where('model_class', class_basename(self::class));
    }

    public function modelAttributeAudits(): HasMany
    {
        return $this->hasMany(ModelAttributeAudit::class, 'object_uid')->where('model_class', class_basename(self::class));
    }

    public function modelRelationshipStatistics(): HasMany
    {
        return $this->hasMany(ModelRelationshipStatistic::class, 'object_uid')->where('model_class', class_basename(self::class));
    }

    /**
     * @return array<int, class-string>
     */
    public function stickleRelationships(): array
    {
        // Get all classes with the StickleEntity trait
        $stickleEntityClasses = ClassUtils::getClassesWithTrait(
            config('stickle.namespaces.models'),
            \StickleApp\Core\Traits\StickleEntity::class
        );

        $relationships = ClassUtils::getRelationshipsWith(app(), self::class, [HasMany::class], $stickleEntityClasses);

        array_walk($relationships, function (&$relationship) {
            $attribute = AttributeUtils::getAttributeForClassMethod(
                self::class,
                $relationship['name'],
                StickleRelationshipMetadata::class
            );

            if ($attribute) {
                $relationship = array_merge($relationship, (array) $attribute->value);
            }
        });

        return $relationships;
    }

    /**
     * Mutator that allows you to set:
     * `$trackedModel->trackable_attributes = ['key' => 'value', 'key2' => 'value2']`
     *
     * It will retrieve or create the one-to-one relationship with the ModelAttributes model
     * and merge the provided attributes with the existing ones and persist it to the database
     */
    protected function trackableAttributes(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->modelAttributes()
                    ->firstOrNew(
                        [
                            'model_class' => class_basenname(self::class),
                            'object_uid' => $this->id,
                        ]
                    )->data ?? [];
            },
            set: function ($value) {
                if (is_array($value)) {
                    $modelAttributes = $this
                        ->modelAttributes()
                        ->firstOrCreate(
                            [
                                'model_class' => class_basename(self::class),
                                'object_uid' => $this->id,
                            ],
                            [
                                'data' => [],
                            ]
                        );
                    $existingAttributes = $modelAttributes->data ?? [];
                    $modelAttributes->update(
                        [
                            'data' => array_merge($existingAttributes, $value),
                            'synced_at' => now(),
                        ]
                    );
                }
            }
        );
    }

    public static function getStickleChartData(): array
    {

        // Get the attributes that are tracked by StickleTrait as keys with empty arrays as values
        $trackedAttributes = static::getStickleTrackedAttributes();
        // Get the metadata [ 'attribute' => [ 'chartType' => 'line', 'label' => 'Attribute', 'description' => 'Description', 'dataType' => 'string', 'primaryAggregateType' => 'sum' ] ]
        $metadata = AttributeUtils::getAttributesForClass(
            static::class,
            StickleAttributeMetadata::class
        );

        // Directly build chart data for tracked attributes
        $chartData = [];
        foreach ($trackedAttributes as $attribute) {
            $meta = $metadata[$attribute] ?? [];
            $chartData[] = [
                'key' => $attribute,
                'modelClass' => static::class,
                'attribute' => $attribute,
                'chartType' => $meta['chartType'] ?? \StickleApp\Core\Enums\ChartType::LINE,
                'label' => $meta['label'] ?? Str::title(str_replace('_', ' ', $attribute)),
                'description' => $meta['description'] ?? null,
                'dataType' => $meta['dataType'] ?? null,
                'primaryAggregateType' => $meta['primaryAggregateType'] ?? null,
            ];
        }

        return $chartData;
    }
}
