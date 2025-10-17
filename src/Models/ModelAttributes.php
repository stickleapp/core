<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $model_class
 * @property string $object_uid
 *
 * @use HasFactory<Factory<static>>
 */
class ModelAttributes extends Model
{
    use HasFactory;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't popoulate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'model_attributes';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'object_uid',
        'model_class',
        'data',
        'synced_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
    ];

    /**
     * Get the parent attributable model
     *
     * @return MorphTo<Model, ModelAttributes>
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The attributes that should be cast.
     *
     * NOTE: We intentionally use the $casts property instead of the casts() method here.
     * While Rector recommends modernizing to casts(), the method approach doesn't work
     * reliably with mass assignment operations like updateOrCreate() and firstOrCreate().
     * The casts() method is not always invoked during these operations, causing arrays
     * to be inserted as raw values instead of being JSON-encoded, which results in
     * "Array to string conversion" errors when inserting into JSONB columns.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];
}
