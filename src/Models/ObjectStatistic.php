<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectStatistic extends Model
{
    public $timestamps = false;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'object_statistics';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'model',
        'object_uid',
        'attribute',
        'value',
        'value_count',
        'value_sum',
        'value_min',
        'value_max',
        'value_avg',
    ];
}
