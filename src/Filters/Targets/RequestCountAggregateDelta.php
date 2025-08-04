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

class RequestCountAggregateDelta extends FilterTargetContract
{
    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public Builder $builder,
        public string $url,
        public string $aggregate,
        public string $deltaVerb,
        public array $previousPeriod,
        public array $currentPeriod
    ) {}

    public static function baseTarget(): string
    {
        return 'StickleApp\\Core\\Filters\\Targets\\RequestCount';
    }

    public function property(): ?string
    {
        return $this->url;
    }

    public function castProperty(): mixed
    {
        return sprintf('request_count_aggregate_delta_%s', $this->joinKey());
    }

    public function joinKey(): string
    {
        // Generate a consistent key based on the filter parameters, not the builder state
        $keyData = [
            $this->url,
            $this->aggregate,
            $this->deltaVerb,
            $this->currentPeriod[0]->format('Y-m-d'),
            $this->currentPeriod[1]->format('Y-m-d'),
            $this->previousPeriod[0]->format('Y-m-d'),
            $this->previousPeriod[1]->format('Y-m-d'),
            $this->builder->getModel()->getMorphClass(),
        ];

        return md5(implode('|', $keyData));
    }

    private function subJoin(): QueryBuilder
    {
        $currentStart = $this->currentPeriod[0]->format('Y-m-d');
        $currentEnd = $this->currentPeriod[1]->format('Y-m-d');
        $previousStart = $this->previousPeriod[0]->format('Y-m-d');
        $previousEnd = $this->previousPeriod[1]->format('Y-m-d');

        return DB::table($this->prefix.'requests_rollup_1day')
            ->where('url', $this->url)
            ->where('model_class', $this->builder->getModel()->getMorphClass())
            ->where(function ($query) use ($currentStart, $currentEnd, $previousStart, $previousEnd) {
                $query->whereBetween('day', [$currentStart, $currentEnd])
                    ->orWhereBetween('day', [$previousStart, $previousEnd]);
            })
            ->groupBy(['model_class', 'object_uid'])
            ->select([
                'model_class',
                'object_uid',
                DB::raw("
                    COALESCE({$this->aggregate}(CASE WHEN day BETWEEN '{$currentStart}' AND '{$currentEnd}' THEN request_count END), 0) -
                    COALESCE({$this->aggregate}(CASE WHEN day BETWEEN '{$previousStart}' AND '{$previousEnd}' THEN request_count END), 0) as {$this->castProperty()}
                "),
            ]);
    }

    public function applyJoin(): void
    {
        $subJoin = $this->subJoin();

        $joinKey = $this->joinKey();

        if ($this->builder->hasJoin($subJoin->toSql(), $joinKey)) {
            return;
        }

        $model = $this->builder->getModel();

        $this->builder->leftJoinSub(
            $this->subJoin(),
            $joinKey,
            function (JoinClause $join) use ($model, $joinKey) {
                $join->on($joinKey.'.object_uid', '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
            }
        );
    }
}
