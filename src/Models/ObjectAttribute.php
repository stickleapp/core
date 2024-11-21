<?php

namespace Dclaysmith\LaravelCascade\Models;

use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ObjectAttribute extends Model
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        $this->table = $this->prefix.'object_attributes';
    }

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
