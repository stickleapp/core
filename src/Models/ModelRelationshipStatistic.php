<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<Factory<static>>
 */
class ModelRelationshipStatistic extends Model
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
        $this->table = config('stickle.database.tablePrefix').'model_relationship_statistics';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'model_class',
        'object_uid',
        'relationship',
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
