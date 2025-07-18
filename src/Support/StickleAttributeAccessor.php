<?php

namespace StickleApp\Core\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StickleAttributeAccessor
{
    protected Model $model;

    protected string $attribute;

    protected ?bool $isAuditMode;

    protected ?Carbon $startDate = null;

    protected ?Carbon $endDate = null;

    protected ?int $limit = null;

    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    /**
     * Filter history between two dates
     */
    public function between(mixed $startDate, mixed $endDate = null): StickleAttributeAccessor
    {
        $this->startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $this->endDate = $endDate ? ($endDate instanceof Carbon ? $endDate : Carbon::parse($endDate)) : now();

        return $this;
    }

    /**
     * Limit the number of records returned
     */
    public function limit(int $limit): StickleAttributeAccessor
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the current value of the attribute
     */
    public function current(): mixed
    {
        /** @phpstan-ignore-next-line */
        $attributes = $this->model->modelAttributes;

        return data_get($attributes, $this->attribute, null);
    }

    /**
     * Get the most recent value from the audit history
     */
    public function latest(): mixed
    {

        return $this->limit(1)->audit()->first();
    }

    /**
     * Get all values as a timeline with timestamps
     *
     * @return Collection<int, array{value: mixed, timestamp: Carbon, old_value?: mixed, change?: string}>
     */
    public function audit(): Collection
    {
        /** @phpstan-ignore-next-line */
        $query = $this->model->modelAttributeAudits()
            ->where('attribute', $this->attribute)
            ->orderBy('created_at', 'desc');

        $query->when($this->startDate, function ($q) {
            $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
        });

        $query->when($this->limit, function ($q, $limit) {
            $q->limit($limit);
        }, $this->limit);

        return $query->get()->map(function ($audit) {
            return [
                'value' => $audit->value_new,
                'old_value' => $audit->value_old,
                'timestamp' => $audit->created_at,
            ];
        });
    }

    /**
     * Implement __toString to return the current value
     */
    public function __toString()
    {
        $value = $this->current();

        return is_scalar($value) ? (string) $value : json_encode($value);
    }
}
