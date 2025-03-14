<?php

namespace StickleApp\Core\Models;

use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property string $model
 * @property string $as_class
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
        'model',
        'definition',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Model, SegmentStatistic>
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
        return $this->belongsToMany(
            $this->model,
            "{$this->prefix}object_segment",
            'segment_id',
            'object_uid')
            ->join($this->model, DB::raw($this->model.'.id::string'), '=', "{$this->prefix}object_segment.object_uid");
    }
}
