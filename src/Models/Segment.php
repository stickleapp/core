<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Carbon\Carbon;
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
 * @property Carbon|null $last_exported_at
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Segment extends Model
{
    use HasFactory;
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
     * @return HasMany<SegmentStatistic, $this>
     */
    public function segmentStatistics(): HasMany
    {
        return $this->hasMany(SegmentStatistic::class);
    }

    /**
     * Get the Objects associated with this Segment
     *
     * @return BelongsToMany<Model, Model>
     */
    public function objects(): BelongsToMany
    {

        $prefix = config('stickle.database.tablePrefix');

        $modelClass = config('stickle.namespaces.models').'\\'.$this->model_class;

        throw_unless(class_exists($modelClass), Exception::class, "Invalid model class specified: {$modelClass}");

        $pivotTable = $prefix.'model_segment';

        // Start with a base relationship
        /** @var class-string<Model> $modelClass */
        $belongsToMany = $this->belongsToMany(
            $modelClass,
            $pivotTable,
            'segment_id',
            'object_uid'
        )->withTimestamps();

        // Get the underlying query builder
        $builder = $belongsToMany->getQuery();

        // Remove the default join constraints
        $joins = $builder->getQuery()->joins;
        if ($joins !== null) {
            $builder->getQuery()->joins = array_filter($joins, fn($join): bool => $join->table !== $pivotTable);
        }

        // Add our custom join with type casting
        /** @var Model $modelInstance */
        $modelInstance = new $modelClass;
        $modelTable = $modelInstance->getTable();
        $primaryKey = $modelInstance->getKeyName();

        $builder->join('stc_model_segment', function ($join) use ($modelTable, $primaryKey): void {
            $join->on(DB::raw($modelTable.'.'.$primaryKey.'::text'), '=', 'stc_model_segment.object_uid')
                ->where('stc_model_segment.segment_id', '=', $this->id);
        });

        return $belongsToMany;
    }
}
