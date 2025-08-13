<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Model;

class LocationData extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ip_address';

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'location_data';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ip_address',
        'city',
        'country',
        'coordinates',
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
            'coordinates' => 'point',
        ];
    }
}
