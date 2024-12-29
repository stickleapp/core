<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Targets;

use DateTimeInterface;
use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Filters\Targets\Traits\HasDeltaFilters;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class EventCountDelta extends FilterTarget
{
    use HasDeltaFilters;

    /**
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function __construct(
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix,
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
                'model',
                'object_uid',
                DB::raw(preg_replace('/\s+/', ' ', "
                    SUM(event_count)
                        OVER (PARTITION BY model, object_uid ORDER BY day RANGE BETWEEN INTERVAL '59 day' PRECEDING AND INTERVAL '30 day' PRECEDING) -
                    SUM(event_count)
                        OVER (PARTITION BY model, object_uid ORDER BY day RANGE BETWEEN INTERVAL '29 day' PRECEDING AND CURRENT ROW) -
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
                $join->on($this->joinKey().'.model', '=', "{$model->getTable()}.model");
            }
        );

        return $builder;
    }
}
