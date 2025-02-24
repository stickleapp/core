<?php

namespace StickleApp\Core\Models;

use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ObjectAttribute extends Model
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't popoulate in Factory
         */
        $this->table = $this->prefix.'object_attributes';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'object_uid',
        'model',
        'model_attributes',
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
        'model_attributes' => 'array',
    ];

    /**
     * @var string
     */
    public $model;

    /**
     * @var string
     */
    public $object_uid;

    /**
     * Get the parent attributable model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, ObjectAttribute>
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }
}
