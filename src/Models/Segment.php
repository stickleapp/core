<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Carbon\Carbon;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use StickleApp\Core\Relations\TextKeyBelongsToMany;
use StickleApp\Core\Support\ClassUtils;

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
 *
 * @use HasFactory<Factory<static>>
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
     * @return BelongsToMany<Model, $this, Pivot>
     */
    public function objects(): BelongsToMany
    {
        $prefix = config('stickle.database.tablePrefix');

        $modelClass = ClassUtils::resolveModelClass($this->model_class);

        /** @var class-string<Model> $modelClass */
        return $this->belongsToMany(
            $modelClass,
            $prefix.'model_segment',
            'segment_id',
            'object_uid'
        )->withTimestamps();
    }

    /**
     * Build the pivot join with the related key cast to text.
     *
     * object_uid is a text column and a model key generally is not, and
     * Postgres will not compare the two without a cast. This is Eloquent's
     * own hook for substituting the relation class, which is why the join is
     * built correctly once here rather than generated and then rewritten.
     *
     * @param  Builder<Model>  $query
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string|null  $relationName
     * @return BelongsToMany<Model, Model, Pivot>
     */
    protected function newBelongsToMany(
        Builder $query,
        Model $parent,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null,
    ) {
        return new TextKeyBelongsToMany(
            $query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName
        );
    }
}
