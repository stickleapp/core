<?php

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SegmentStatisticExport extends Model
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'segment_statistics_export';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'segment_id',
        'attribute',
        'last_recorded_at',
    ];
}
