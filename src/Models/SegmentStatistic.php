<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class SegmentStatistic
 *
 * @property int $segment_id
 * @property string $attribute
 * @property string $value
 * @property int $value_count
 * @property float $value_sum
 * @property float $value_min
 * @property float $value_max
 * @property float $value_avg
 * @property Carbon $recorded_at
 *
 * @use HasFactory<Factory<static>>
 */
class SegmentStatistic extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
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
        'recorded_at',
    ];
}
