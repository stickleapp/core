<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Override;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $type
 * @property string $model_class
 * @property string $object_uid
 * @property string $session_uid
 * @property string|null $location
 * @property string $ip_address
 * @property array<string, mixed>|null $properties
 * @property Carbon $timestamp
 */
class Request extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     */
    protected $table;

    /**
     * This table does not use timestamps.
     */
    public $timestamps = false;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] public ?string $prefix = null,
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        if (! $this->table) {
            $this->table = config('stickle.database.tablePrefix').'requests';
        }

        parent::__construct();
    }

    /**
     * Get the table associated with the model.
     */
    #[Override]
    public function getTable()
    {
        if (! $this->table) {
            $this->table = config('stickle.database.tablePrefix').'requests';
        }

        return $this->table;
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'model_class',
        'object_uid',
        'session_uid',
        'ip_address',
        'properties',
        'timestamp',
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
            'properties' => 'json',
            'timestamp' => 'datetime',
        ];
    }

    /**
     * Get the location data associated with the request.
     *
     * @return BelongsTo<LocationData, $this>
     */
    public function locationData()
    {
        return $this->belongsTo(LocationData::class, 'ip_address', 'ip_address');
    }
}
