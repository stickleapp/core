<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $model_class
 * @property string $object_uid
 */
class ModelAttributes extends Model
{
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
     * Get the attributes that should be cast.
     *
     * Why doesn't casts() function work?
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the parent attributable model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, ModelAttributes>
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }
}
