<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Filters\Targets;

use Dclaysmith\LaravelCascade\Contracts\FilterTarget;
use Dclaysmith\LaravelCascade\Filters\Targets\Traits\HasDeltaFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class EventCountDelta extends FilterTarget
{
    use HasDeltaFilters;

    public function __construct(public string $event, public array $dateRange) {}

    public function property(): ?string
    {
        return $this->event;
    }

    public function applyJoin(Builder $builder): Builder
    {
        if (array_key_exists($this->joinKey(), $this->joins)) {
            return $builder;
        }

        $model = $builder->getModel();

        $builder->joinSub(\DB::table('events'), $this->joinKey(), function (JoinClause $join) use ($model) {
            $join->on($this->joinKey().'.object_uid', '=', "{$model->getTable()}.object_uid");
            $join->on($this->joinKey().'.model', '=', "{$model->getTable()}.model");
        });

        $this->joins[] = $this->joinKey();

        return $builder;
    }
}
