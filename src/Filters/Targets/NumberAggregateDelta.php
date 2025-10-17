<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use DateTimeInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Override;
use StickleApp\Core\Contracts\FilterTargetContract;

class NumberAggregateDelta extends FilterTargetContract
{
    /**
     * @param  Builder<Model>  $builder
     * @param  array<DateTimeInterface>  $currentPeriod
     * @param  array<DateTimeInterface>  $previousPeriod
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public Builder $builder,
        public string $attribute,
        public string $aggregate,
        public string $deltaVerb,
        public array $previousPeriod,
        public array $currentPeriod
    ) {}

    public static function baseTarget(): string
    {
        return Number::class;
    }

    public function property(): ?string
    {
        return $this->attribute;
    }

    #[Override]
    public function castProperty(): mixed
    {
        return sprintf('number_aggregate_delta_%s', $this->joinKey());
    }

    public function joinKey(): string
    {
        // Generate a consistent key based on the filter parameters, not the builder state
        $keyData = [
            $this->attribute,
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

        return DB::table($this->prefix.'model_attributes')
            ->where('model_class', $this->builder->getModel()->getMorphClass())
            ->where(function (\Illuminate\Contracts\Database\Query\Builder $builder) use ($currentStart, $currentEnd, $previousStart, $previousEnd): void {
                $builder->whereBetween('updated_at', [$currentStart, $currentEnd])
                    ->orWhereBetween('updated_at', [$previousStart, $previousEnd]);
            })
            ->groupBy(['model_class', 'object_uid'])
            ->select([
                'model_class',
                'object_uid',
                DB::raw("
                    COALESCE({$this->aggregate}(CASE WHEN updated_at BETWEEN '{$currentStart}' AND '{$currentEnd}' THEN (data->>'{$this->attribute}')::numeric END), 0) -
                    COALESCE({$this->aggregate}(CASE WHEN updated_at BETWEEN '{$previousStart}' AND '{$previousEnd}' THEN (data->>'{$this->attribute}')::numeric END), 0) as {$this->castProperty()}
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
            function (JoinClause $joinClause) use ($model, $joinKey): void {
                $joinClause->on($joinKey.'.object_uid', '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
            }
        );
    }
}
