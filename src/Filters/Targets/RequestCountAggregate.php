<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Targets;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTargetContract;

class RequestCountAggregate extends FilterTargetContract
{
    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')] protected ?string $prefix,
        public Builder $builder,
        public string $url,
        public string $aggregate,
        public ?DateTimeInterface $startDate = null,
        public ?DateTimeInterface $endDate = null
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
        return sprintf('request_%s_%s', $this->aggregate, $this->joinKey());
    }

    public function joinKey(): string
    {
        // Generate a consistent key based on the filter parameters, not the builder state
        $keyData = [
            'url' => $this->url,
            'aggregate' => $this->aggregate,
            'startDate' => $this->startDate?->format('Y-m-d'),
            'endDate' => $this->endDate?->format('Y-m-d'),
            'modelClass' => $this->builder->getModel()->getMorphClass(),
        ];

        return md5(implode('|', array_values($keyData)));
    }

    private function subJoin(): QueryBuilder
    {
        return \DB::table($this->prefix.'requests_rollup_1day')
            ->where('type', 'event')
            ->where('url', $this->url)
            ->where('model_class', $this->builder->getModel()->getMorphClass())
            ->whereDate('day', '>=', Carbon::parse($this->startDate)->toDateString())
            ->whereDate('day', '<', Carbon::parse($this->endDate)->toDateString())
            ->groupBy(['model_class', 'object_uid'])
            ->select('model_class', 'object_uid', DB::raw("{$this->aggregate}(request_count) as {$this->castProperty()}"));
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
