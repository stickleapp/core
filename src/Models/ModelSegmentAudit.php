<?php

declare(strict_types=1);

namespace StickleApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelSegmentAudit extends Model
{
    use HasFactory;
    public $timestamps = false;

    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
    ) {
        /**
         * We aren't using the Attribute\Config trait b/c it doesn't populate in Factory
         */
        $this->table = config('stickle.database.tablePrefix').'model_segment_audit';
    }

    /**
     * The attributes that are mass assignable.
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
     */
    protected $hidden = [
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * @var string
     */
    public $model;

    /**
     * @var string
     */
    public $object_uid;

    /**
     * Get the Object associated with the audit
     *
     * @return Attribute<Model, string>
     */
    protected function object(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->segment === null) {
                    return null;
                }

                $modelClass = $this->segment->model_class;

                return $modelClass::find($this->object_uid);
            }
        );
    }

    /**
     * Get the Segment associated with the audit
     *
     * @return BelongsTo<Segment, $this>
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }
}
