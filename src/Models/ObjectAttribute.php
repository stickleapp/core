<?php

namespace Dclaysmith\LaravelCascade\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ObjectAttribute extends Model
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't popoulate in Factory
         */
        $this->table = config('cascade.database.tablePrefix').'object_attributes';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'object_uid',
        'model',
        'model_attributes',
        'synced_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'model_attributes' => 'array',
    ];
    // protected function casts(): array
    // {

    //     return [
    //         'model_attributes' => 'array',
    //     ];
    // }

    /**
     * Get the parent attributable model
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }
}
