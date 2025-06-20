<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use DateTimeInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;
use StickleApp\Core\Filters\Targets\Traits\HasDeltaFilters;

class EventCountDelta extends FilterTargetContract
{
    use HasDeltaFilters;

    /**
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public string $event,
        public ?array $currentPeriod,
        public ?array $previousPeriod
    ) {}

    public function property(): ?string
    {
        return $this->event;
    }

    public function joinKey(): ?string
    {
        return md5($this->subJoin()->toSql().json_encode($this->subJoin()->getBindings()));
    }

    private function subJoin(): QueryBuilder
    {
        return \DB::table($this->prefix.'events_rollup_1day')
            ->where('event_name', $this->event)
            ->select(
                'model_class',
                'object_uid',
                DB::raw(preg_replace('/\s+/', ' ', "
                    SUM(event_count)
                        OVER (PARTITION BY model_class, object_uid ORDER BY day RANGE BETWEEN INTERVAL '59 day' PRECEDING AND INTERVAL '30 day' PRECEDING) -
                    SUM(event_count)
                        OVER (PARTITION BY model_class, object_uid ORDER BY day RANGE BETWEEN INTERVAL '29 day' PRECEDING AND CURRENT ROW) -
                    AS delta
                "))
            );
    }

    public function applyJoin(Builder $builder): Builder
    {
        if (! $this->joinKey()) {
            return $builder;
        }

        if ($builder->hasJoin($this->subJoin()->toSql(), $this->joinKey())) {
            return $builder;
        }

        $model = $builder->getModel();

        $builder->leftJoinSub(
            $this->subJoin(),
            $this->joinKey(),
            function (JoinClause $join) use ($model) {
                $join->on($this->joinKey().'.object_uid', '=', "{$model->getTable()}.object_uid");
                $join->on($this->joinKey().'.model_class', '=', "{$model->getTable()}.model_class");
            }
        );

        return $builder;
    }
}
