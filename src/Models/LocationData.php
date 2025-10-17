<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StickleApp\Core\Casts\PostGISPoint;

/**
 * @use HasFactory<Factory<static>>
 */
class LocationData extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ip_address';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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

        parent::__construct();
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
            'ip_address' => 'string',
            'coordinates' => PostGISPoint::class,
        ];
    }

    /**
     * @return HasMany<Request, $this>
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'ip_address', 'ip_address');
    }
}
