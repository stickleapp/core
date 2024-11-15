<?php

namespace Dclaysmith\LaravelCascade\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ObjectAttribute extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = config('cascade.database.tablePrefix').'object_attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * Get the parent attributable model
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }
}
