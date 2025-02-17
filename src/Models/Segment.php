<?php

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Segment extends Model
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't popoulate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'segments';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     *
     * @var array<int, string>
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
     */
    public function segmentStatistics(): HasMany
    {
        return $this->hasMany(SegmentStatistic::class);
    }

    /**
     * Get the SegmentStatistics associated with the Segment
     */
    public function objects(): BelongsToMany
    {
        return $this->belongsToMany($this->model, 'lc_object_segment', 'segment_id', 'object_uid')
            ->join('users', DB::raw('users.id::string'), '=', 'lc_object_segment.object_uid');
    }
}
