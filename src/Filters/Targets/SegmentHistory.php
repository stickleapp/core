<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Models\Segment as SegmentModel;

class SegmentHistory extends FilterTargetContract
{
    public int $segmentId;

    protected string $modelSegmentAuditTable;

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function __construct(
        protected ?string $prefix,
        public Builder $builder,
        public string $segmentIdentifier
    ) {
        $this->modelSegmentAuditTable = $this->prefix.'model_segment_audit';
        $this->segmentId = $this->resolveSegmentId($segmentIdentifier);
    }

    public function property(): ?string
    {
        return $this->modelSegmentAuditTable.'.segment_id';
    }

    public function castProperty(): mixed
    {
        return $this->property();
    }

    public function applyJoin(): void
    {
        $modelTable = $this->builder->getModel()->getTable();
        $primaryKey = $this->builder->getModel()->getKeyName();

        // Check if join already exists to avoid duplicate joins
        $existingJoins = $this->builder->getQuery()->joins ?? [];
        $joinExists = collect($existingJoins)->contains(function ($join) {
            return $join->table === $this->modelSegmentAuditTable;
        });

        if (! $joinExists) {
            $this->builder->leftJoin($this->modelSegmentAuditTable, function ($join) use ($modelTable, $primaryKey) {
                $join->on(DB::raw($modelTable.'.'.$primaryKey.'::text'), '=', $this->modelSegmentAuditTable.'.object_uid')
                    ->where($this->modelSegmentAuditTable.'.segment_id', '=', $this->segmentId)
                    ->where($this->modelSegmentAuditTable.'.operation', '=', 'ENTER');
            });
        }
    }

    /**
     * Resolve segment identifier to segment ID
     */
    protected function resolveSegmentId(string $identifier): int
    {
        // If it's numeric, assume it's already an ID
        if (is_numeric($identifier)) {
            return (int) $identifier;
        }

        // Otherwise, look up by name or as_class
        $segment = SegmentModel::where('name', $identifier)
            ->orWhere('as_class', $identifier)
            ->first();

        if (! $segment) {
            throw new \InvalidArgumentException("Segment not found: {$identifier}");
        }

        return $segment->id;
    }
}
