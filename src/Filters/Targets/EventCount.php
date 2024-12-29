<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Targets;

use Carbon\Carbon;
use DateTimeInterface;
use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Filters\Targets\Traits\HasDeltaFilters;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class EventCount extends FilterTarget
{
    use HasDeltaFilters;

    public function __construct(
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix,
        public string $event,
        public ?DateTimeInterface $startDate = null,
        public ?DateTimeInterface $endDate = null
    ) {}

    public function property(): ?string
    {
        return $this->event;
    }

    public function joinKey(): ?string
    {
        $subJoin = $this->subJoin();

        return md5($subJoin->toSql().json_encode($subJoin->getBindings()));
    }

    private function subJoin(): QueryBuilder
    {
        return \DB::table($this->prefix.'events_rollup_1day')
            ->where('event_name', $this->event)
            ->whereDate('day', '>=', Carbon::parse($this->startDate)->toDateString())
            ->whereDate('day', '<', Carbon::parse($this->endDate)->toDateString())
            ->groupBy(['model', 'object_uid'])
            ->select('model', 'object_uid', DB::raw('sum(event_count) as event_count'));
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

    public function startDate(DateTimeInterface $date): void
    {
        $this->startDate = $date;
    }

    public function endDate(DateTimeInterface $date): void
    {
        $this->endDate = $date;
    }

    public function between(DateTimeInterface $startDate, DateTimeInterface $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}
