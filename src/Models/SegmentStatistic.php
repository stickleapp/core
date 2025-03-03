<?php

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SegmentStatistic extends Model
{
    public $timestamps = false;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't popoulate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'segment_statistics';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'segment_id',
        'attribute',
        'value',
        'value_count',
        'value_sum',
        'value_min',
        'value_max',
        'value_avg',
    ];
}
