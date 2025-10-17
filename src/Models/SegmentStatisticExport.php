<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<Factory<static>>
 */
class SegmentStatisticExport extends Model
{
    use HasFactory;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'segment_statistic_exports';
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
