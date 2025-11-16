<?php

declare(strict_types=1);

namespace StickleApp\Core\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Attributes\StickleObservedAttribute;
use StickleApp\Core\Attributes\StickleRelationshipMetadata;
use StickleApp\Core\Attributes\StickleTrackedAttribute;
use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Filters\Base as Filter;
use StickleApp\Core\Models\ModelAttributeAudit;
use StickleApp\Core\Models\ModelAttributes;
use StickleApp\Core\Models\ModelRelationshipStatistic;
use StickleApp\Core\Support\AttributeUtils;
use StickleApp\Core\Support\ClassUtils;

/**
 * Trait StickleEntity
 *
 * This trait provides methods to enable Stickle functionality on Eloquent models.
 * It allows for filtering, tracking attributes, and managing relationships with Stickle.
 */
trait StickleEntity
{
    public static function getTableName()
    {
        return (new self)->getTable();
    }

    public static function bootStickleEntity(): void
    {

        /**
         * Used when building queries to prevent duplicate joins
         */
        Builder::macro('hasJoin', fn ($table, $alias = null) => collect($this->getQuery()->joins)->contains(function ($join) use ($table, $alias): bool {
            if ($join->table instanceof Expression) {
                return $join->table->getValue($join->getGrammar()) === "({$table}) as \"{$alias}\"";
            }

            return $join->table === $table;
        }));

        Builder::macro('joinRelationship', function (Relation $relation, string $alias, string $joinType = 'inner'): object {

            $relatedTable = $relation->getRelated()->getTable();
            $parentTable = $relation->getParent()->getTable();

            // Handle different relationship types
            if ($relation instanceof HasOneOrMany) {
                $foreignKey = $relation->getQualifiedForeignKeyName();
                $localKey = $relation->getQualifiedParentKeyName();
            } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                $foreignKey = $relation->getQualifiedForeignKeyName();
                $localKey = $relation->getQualifiedOwnerKeyName();
            } else {
                throw new Exception('Unsupported relation type: '.$relation::class);
            }

            // If the related table is aliased, we need to adjust the foreign key
            $foreignKey = str_replace($relatedTable, $alias, $relation->getQualifiedForeignKeyName());

            // Get the basic query constraints (like where clauses) from the relation
            $builder = $relation->getQuery();
            $wheres = $builder->getQuery()->wheres;

            // Apply the join
            $tableToJoin = $alias !== '' && $alias !== '0' ? "$relatedTable as $alias" : $relatedTable;
            $joinTable = $alias ?: $relatedTable;

            $this->join($tableToJoin, function ($join) use ($foreignKey, $localKey, $wheres, $joinTable): void {
                $join->on($foreignKey, '=', $localKey);

                // Apply any additional constraints from the relationship
                foreach ($wheres as $where) {
                    if ($where['type'] === 'Basic') {
                        // Adjust column name if it doesn't include a table prefix
                        $column = $where['column'];
                        if (! str_contains($column, '.')) {
                            $column = "$joinTable.$column";
                        }

                        $join->where($column, $where['operator'], $where['value'], $where['boolean']);
                    }
                }
            }, null, null, $joinType);

            return $this;
        });

        /**
         * When a StickleEntity model is created, log observed attributes
         */
        static::created(function (Model $model): void {

            $observableAttributeKeys = $model::stickleObservedAttributes();

            $model->trackable_attributes = $model->only($observableAttributeKeys);
        });

        /**
         * When a stickleEntity model is updated, log observed attributes
         */
        static::updated(function (Model $model): void {

            $observableAttributeKeys = array_intersect($model::stickleObservedAttributes(), array_keys($model->getDirty()));

            $model->trackable_attributes = $model->only($observableAttributeKeys);
        });

    }

    /**
     * Enables a ->stickleWhere() method on the model
     */
    public static function scopeStickleWhere(Builder $builder, Filter $filter): Builder
    {

        $prefix = config('stickle.database.tablePrefix');

        /**
         * We'll need this join for the filters but do not want to add it twice
         */
        if (! $builder->hasJoin("{$prefix}model_attributes")) {
            $builder->leftJoin("{$prefix}model_attributes", function ($join) use ($prefix): void {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw(self::getTableName().'.id::text'));
                $join->where("{$prefix}model_attributes.model_class", '=', self::class);
            });
        }

        return $filter->apply($builder, 'and');
    }

    /**
     * Enables a ->stickleOrWhere() method on the model
     */
    public static function scopeStickleOrWhere(Builder $builder, Filter $filter): Builder
    {
        $prefix = config('stickle.database.tablePrefix');

        /**
         * We'll need this join for the filters but do not want to add it twice
         */
        if (! $builder->hasJoin("{$prefix}model_attributes")) {
            $builder->leftJoin("{$prefix}model_attributes", function ($join) use ($prefix): void {
                $join->on("{$prefix}model_attributes.object_uid", '=', DB::raw(self::getTableName().'.'.self::getKeyName().'::text'));
                $join->where("{$prefix}model_attributes.model_class", '=', self::class);
            });
        }

        return $filter->apply($builder, 'or');
    }

    public function stickleLabel(): string
    {
        return Str::of(strtolower(
            class_basename(self::class)
        ))->headline().' '.$this->getKey();
    }

    public function stickleUrl(): string
    {
        return route('stickle::model', [
            'modelClass' => class_basename(self::class),
            'uid' => $this->getKey(),
        ]);
    }

    /**
     * Helper method to extract attribute names from properties and methods
     * that have the specified PHP attribute.
     *
     * @param  class-string  $attributeClass
     */
    protected static function getAttributesWithAttribute(string $attributeClass): array
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $attributes = [];

        // Check properties for the attribute
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyAttributes = $property->getAttributes($attributeClass);
            if (! empty($propertyAttributes)) {
                $attributes[] = $property->getName();
            }
        }

        // Check methods for the attribute (for Eloquent accessors)
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $method) {
            $methodAttributes = $method->getAttributes($attributeClass);
            if (! empty($methodAttributes)) {
                // Convert camelCase method name to snake_case attribute name
                $attributeName = Str::snake($method->getName());
                $attributes[] = $attributeName;
            }
        }

        return array_unique($attributes);
    }

    /**
     * Define which attributes should be watched for changes.
     * Override this method in your model to specify observed attributes,
     * or use the #[StickleObservedAttribute] attribute on properties/methods.
     */
    public static function stickleObservedAttributes(): array
    {
        return static::getAttributesWithAttribute(StickleObservedAttribute::class);
    }

    /**
     * Define which attributes should be tracked over time for analytics.
     * Override this method in your model to specify tracked attributes,
     * or use the #[StickleTrackedAttribute] attribute on properties/methods.
     */
    public static function stickleTrackedAttributes(): array
    {
        return static::getAttributesWithAttribute(StickleTrackedAttribute::class);
    }

    /**
     * @return HasOne<ModelAttributes, $this>
     */
    public function modelAttributes(): HasOne
    {
        return $this->hasOne(ModelAttributes::class, 'object_uid')->where('model_class', class_basename(self::class));
    }

    /**
     * @return HasMany<ModelAttributeAudit, $this>
     */
    public function modelAttributeAudits(): HasMany
    {
        return $this->hasMany(ModelAttributeAudit::class, 'object_uid')->where('model_class', class_basename(self::class));
    }

    /**
     * @return HasMany<ModelRelationshipStatistic, $this>
     */
    public function modelRelationshipStatistics(): HasMany
    {
        return $this->hasMany(ModelRelationshipStatistic::class, 'object_uid')->where('model_class', class_basename(self::class));
    }

    /**
     * @return array<int, class-string>
     */
    public function stickleRelationships(?array $relations = []): Collection
    {

        $relations ??= [HasMany::class, BelongsTo::class];

        // Get all classes with the StickleEntity trait
        $stickleEntityClasses = ClassUtils::getClassesWithTrait(
            config('stickle.namespaces.models'),
            StickleEntity::class
        );

        $relationships = ClassUtils::getRelationshipsWith(app(), self::class, $relations, $stickleEntityClasses);

        array_walk($relationships, function (array &$relationship): void {
            $attribute = AttributeUtils::getAttributeForMethod(
                self::class,
                $relationship['name'],
                StickleRelationshipMetadata::class
            );

            if ($attribute) {
                $relationship = array_merge($relationship, (array) $attribute->value);
            }

            $relationship = (object) $relationship;
        });

        return collect($relationships);
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
            get: function (): void {
                $this->modelAttributes()
                    ->firstOrNew(
                        [
                            'model_class' => class_basename(self::class),
                            'object_uid' => $this->id,
                        ]
                    )->data ?? [];
            },
            set: function ($value): void {
                if (is_array($value)) {
                    $this
                        ->modelAttributes()
                        ->updateOrCreate(
                            [
                                'model_class' => class_basename(self::class),
                                'object_uid' => $this->id,
                            ],
                            [
                                'data' => array_merge(
                                    $this->modelAttributes()->value('data') ?? [],
                                    $value
                                ),
                                'synced_at' => now(),
                            ]
                        );
                }
            }
        );
    }

    public function stickleAttribute(string $attribute): mixed
    {
        /** @phpstan-ignore-next-line */
        $modelAttributesObject = $this->modelAttributes;

        if (! $modelAttributesObject) {
            return null;
        }

        return data_get($modelAttributesObject->data, $attribute, null);
    }

    public static function getStickleChartData(): array
    {

        // Get the attributes that are tracked by StickleTrait as keys with empty arrays as values
        $trackedAttributes = static::stickleTrackedAttributes();

        // Get the metadata [ 'attribute' => [ 'chartType' => 'line', 'label' => 'Attribute', 'description' => 'Description', 'dataType' => 'string', 'primaryAggregateType' => 'sum' ] ]
        $metadataMethods = AttributeUtils::getAllAttributesForClass_targetMethod(
            static::class,
            StickleAttributeMetadata::class
        );

        $metadataProperties = AttributeUtils::getAllAttributesForClass_targetProperty(
            static::class,
            StickleAttributeMetadata::class
        );

        $metadata = array_merge($metadataMethods, $metadataProperties);

        // Directly build chart data for tracked attributes
        $chartData = [];
        foreach ($trackedAttributes as $trackedAttribute) {
            $meta = $metadata[$trackedAttribute] ?? [];
            $chartData[] = [
                'key' => $trackedAttribute,
                'modelClass' => static::class,
                'attribute' => $trackedAttribute,
                'chartType' => $meta['chartType'] ?? ChartType::LINE,
                'label' => $meta['label'] ?? Str::title(str_replace('_', ' ', $trackedAttribute)),
                'description' => $meta['description'] ?? null,
                'dataType' => $meta['dataType'] ?? null,
                'primaryAggregateType' => $meta['primaryAggregateType'] ?? null,
            ];
        }

        return $chartData;
    }
}
