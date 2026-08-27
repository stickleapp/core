<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Override;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Models\Segment as SegmentModel;

class Segment extends FilterTargetContract
{
    public int $segmentId;

    protected string $modelSegmentTable;

    /**
     * @param  Builder<Model>  $builder
     */
    public function __construct(
        protected ?string $prefix,
        public Builder $builder,
        public string $segmentIdentifier
    ) {
        $this->modelSegmentTable = $this->prefix.'model_segment';
        $this->segmentId = $this->resolveSegmentId($segmentIdentifier);
    }

    /**
     * The alias this target's join is given.
     *
     * A segment join is distinguished by its segment_id, which lives inside
     * the join condition rather than in the table name. Keying the alias on
     * the segment lets two different segments coexist in one query, while two
     * filters on the SAME segment still share a single join.
     */
    public function joinAlias(): string
    {
        return $this->modelSegmentTable.'_'.$this->segmentId;
    }

    public function property(): string
    {
        return $this->joinAlias().'.segment_id';
    }

    #[Override]
    public function castProperty(): mixed
    {
        return $this->property();
    }

    public function applyJoin(): void
    {
        $modelTable = $this->builder->getModel()->getTable();
        $primaryKey = $this->builder->getModel()->getKeyName();
        $alias = $this->joinAlias();
        $joinExpression = $this->modelSegmentTable.' as '.$alias;

        if ($this->builder->hasJoin($joinExpression)) {
            return;
        }

        $this->builder->leftJoin($joinExpression, function ($join) use ($modelTable, $primaryKey, $alias): void {
            $join->on(DB::raw($modelTable.'.'.$primaryKey.'::text'), '=', $alias.'.object_uid')
                ->where($alias.'.segment_id', '=', $this->segmentId);
        });
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
        $segment = SegmentModel::query()->where('name', $identifier)
            ->orWhere('as_class', $identifier)
            ->first();

        throw_unless($segment, InvalidArgumentException::class, "Segment not found: {$identifier}");

        return $segment->id;
    }
}
