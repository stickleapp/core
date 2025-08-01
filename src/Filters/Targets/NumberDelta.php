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
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
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
        return 'StickleApp\\Core\\Filters\\Targets\\Number';
    }

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
        // Generate a consistent key based on the filter parameters, not the builder state
        $keyData = [
            'attribute' => $this->attribute,
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate?->format('Y-m-d'),
            'modelClass' => $this->builder->getModel()->getMorphClass(),
        ];

        return md5(json_encode($keyData));
    }

    private function subJoin(): QueryBuilder
    {
        $query = \DB::table($this->prefix.'model_attribute_audit')
            ->where('attribute', $this->attribute)
            ->when($this->endDate, function (QueryBuilder $query) {
                return $query->whereBetween('day', [
                    $this->startDate->format('Y-m-d'),
                    $this->endDate->format('Y-m-d'),
                ]);
            }, function (QueryBuilder $query) {
                return $query->whereDate('day', '>=', $this->startDate->format('Y-m-d'));
            });

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
            function (JoinClause $join) use ($model, $joinKey) {
                $join->on($joinKey.'.object_uid', '=', DB::raw("{$model->getTable()}.{$model->getKeyName()}::text"));
            }
        );
    }
}
