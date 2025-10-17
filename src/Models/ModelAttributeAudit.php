<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @use HasFactory<Factory<static>>
 */
class ModelAttributeAudit extends Model
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
        $this->table = config('stickle.database.tablePrefix').'model_attribute_audit';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'object_uid',
        'model_class',
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
     * @return MorphTo<Model, Model>
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }
}
