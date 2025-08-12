<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $model_class
 * @property string $as_class
 */
class Request extends Model
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'segments';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'model_class',
        'object_uid',
        'session_uid',
        'ip_address',
        'properties',
    ];

    /**
     * The attributes that should be hidden for serialization.
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
            'properties' => 'json',
            'timestamp' => 'datetime',
        ];
    }
}
