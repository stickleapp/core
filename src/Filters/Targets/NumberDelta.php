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

class NumberDelta extends FilterTargetContract
{
    /**
     * @param  array<DateTimeInterface>  $period
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public string $attribute,
        public ?array $period,
    ) {}

    public function property(): ?string
    {
        return "data->>'{$this->attribute}'";
    }

    public function castProperty(): mixed
    {
        return sprintf('%s::numeric', $this->property());
    }

    public function joinKey(): ?string
    {
        return md5($this->subJoin()->toSql().json_encode($this->subJoin()->getBindings()));
    }

    private function subJoin(): QueryBuilder
    {
        $query = \DB::table($this->prefix.'model_attribute_audit')
            ->where('attribute', $this->attribute);

        if ($this->period && count($this->period) === 2) {
            $query->whereBetween('day', [
                $this->period[0]->format('Y-m-d'),
                $this->period[1]->format('Y-m-d'),
            ]);
        }

        return $query->select(
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
