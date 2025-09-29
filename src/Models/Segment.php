<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int|null $segment_group_id
 * @property string $name
 * @property string|null $description
 * @property string $model_class
 * @property string|null $as_class
 * @property array<string, mixed>|null $as_json
 * @property int $export_interval
 * @property \Carbon\Carbon|null $last_exported_at
 * @property int $sort_order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Segment extends Model
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'segments';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'segment_group_id',
        'name',
        'description',
        'model_class',
        'as_class',
        'as_json',
        'export_interval',
        'last_exported_at',
        'sort_order',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * Get the SegmentStatistics associated with the Segment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<SegmentStatistic, Segment>
     */
    public function segmentStatistics(): HasMany
    {
        return $this->hasMany(SegmentStatistic::class);
    }

    /**
     * Get the Objects associated with this Segment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Model, Model>
     */
    public function objects(): BelongsToMany
    {

        $prefix = config('stickle.database.tablePrefix');

        $modelClass = config('stickle.namespaces.models').'\\'.$this->model_class;

        if (! class_exists($modelClass)) {
            throw new \Exception("Invalid model class specified: {$modelClass}");
        }

        $pivotTable = $prefix.'model_segment';

        // Start with a base relationship
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $relation = $this->belongsToMany(
            $modelClass,
            $pivotTable,
            'segment_id',
            'object_uid'
        )->withTimestamps();

        // Get the underlying query builder
        $query = $relation->getQuery();

        // Remove the default join constraints
        $joins = $query->getQuery()->joins;
        if ($joins !== null) {
            $query->getQuery()->joins = array_filter($joins, function ($join) use ($pivotTable) {
                return $join->table !== $pivotTable;
            });
        }

        // Add our custom join with type casting
        /** @var \Illuminate\Database\Eloquent\Model $modelInstance */
        $modelInstance = new $modelClass;
        $modelTable = $modelInstance->getTable();
        $primaryKey = $modelInstance->getKeyName();

        $query->join('stc_model_segment', function ($join) use ($modelTable, $primaryKey) {
            $join->on(DB::raw($modelTable.'.'.$primaryKey.'::text'), '=', 'stc_model_segment.object_uid')
                ->where('stc_model_segment.segment_id', '=', $this->id);
        });

        return $relation;
    }
}
