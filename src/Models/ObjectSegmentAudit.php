<?php

namespace Dclaysmith\LaravelCascade\Models;

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
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't popoulate in Factory
         */
        $this->table = config('cascade.database.tablePrefix').'object_segment_audit';
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
     */
    protected function object(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->segment->model::find($this->object_uid);
            },
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
