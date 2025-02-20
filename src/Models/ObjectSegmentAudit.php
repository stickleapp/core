<?php

namespace StickleApp\Core\Models;

use Illuminate\Container\Attributes\Config as ConfigAttribute;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ObjectSegmentAudit extends Model
{
    public $timestamps = false;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[ConfigAttribute('stickle.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't popoulate in Factory
         */
        $this->table = $this->prefix.'object_segment_audit';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'object_uid',
        'segment_id',
        'operation',
        'recorded_at',
        'event_processed_at',
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
     * Get the Object associated with the audit
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Model, string>
     */
    protected function object(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->segment->model::find($this->object_uid)
        );
    }

    /**
     * Get the Segment associated with the audit
     */
    public function segment(): HasOne
    {
        return $this->hasOne(Segment::class, 'id', 'segment_id');
    }
}
