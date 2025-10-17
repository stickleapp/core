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

class NumberDelta extends FilterTargetContract
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public Builder $builder,
        public string $attribute,
        public DateTimeInterface $startDate,
        public ?DateTimeInterface $endDate = null
    ) {}

    public static function baseTarget(): string
    {
        return Number::class;
    }

    public function property(): ?string
    {
        return "data->>'{$this->attribute}'";
    }

    #[Override]
    public function castProperty(): mixed
    {
        return sprintf('%s::numeric', $this->property());
    }

    public function joinKey(): string
    {
        // Generate a consistent key based on the filter parameters, not the builder state
        $keyData = [
            'attribute' => $this->attribute,
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate?->format('Y-m-d'),
            'modelClass' => $this->builder->getModel()->getMorphClass(),
        ];

        return md5(implode('|', array_values($keyData)));
    }

    private function subJoin(): QueryBuilder
    {
        $builder = DB::table($this->prefix.'model_attribute_audit')
            ->where('attribute', $this->attribute)
            ->when($this->endDate instanceof DateTimeInterface, function (QueryBuilder $queryBuilder) {
                assert($this->endDate instanceof DateTimeInterface); // PHPStan hint

                return $queryBuilder->whereBetween('day', [
                    $this->startDate->format('Y-m-d'),
                    $this->endDate->format('Y-m-d'),
                ]);
            }, fn (QueryBuilder $queryBuilder) => $queryBuilder->whereDate('day', '>=', $this->startDate->format('Y-m-d')));

        return $builder->select(
            'model_class',
            'object_uid',
            DB::raw(preg_replace('/\s+/', ' ', "
                LAST_VALUE(({$this->attribute})::numeric) 
                    OVER (PARTITION BY model_class, object_uid ORDER BY day ASC ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) - 
                FIRST_VALUE(({$this->attribute})::numeric) 
                    OVER (PARTITION BY model_class, object_uid ORDER BY day ASC)
                AS delta
            "))
        );
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
            $subJoin,
            $joinKey,
            function (JoinClause $joinClause) use ($model, $joinKey): void {
                $joinClause->on($joinKey.'.object_uid', '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
            }
        );
    }
}
