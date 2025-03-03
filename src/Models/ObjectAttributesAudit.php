<?php

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ObjectAttributesAudit extends Model
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
        $this->table = config('stickle.database.tablePrefix').'object_attributes_audit';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'object_uid',
        'model',
        'attribute',
        'value_old',
        'value_new',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * Get the parent attributable model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, Model>
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }
}
