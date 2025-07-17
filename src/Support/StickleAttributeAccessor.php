<?php

namespace StickleApp\Core\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StickleAttributeAccessor
{
    protected $model;

    protected $attribute;

    protected $isAuditMode = false;

    protected $startDate = null;

    protected $endDate = null;

    protected $limit = null;

    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    /**
     * Get the current value of the attribute
     */
    public function current()
    {
        $attributes = $this->model->modelAttributes;

        return $attributes && isset($attributes->data[$this->attribute])
            ? $attributes->data[$this->attribute]
            : null;
    }

    /**
     * Switch to audit mode for historical queries
     */
    public function audit()
    {
        $this->isAuditMode = true;

        return $this;
    }

    /**
     * Filter history between two dates
     */
    public function between($startDate, $endDate = null)
    {
        $this->startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $this->endDate = $endDate ? ($endDate instanceof Carbon ? $endDate : Carbon::parse($endDate)) : now();

        return $this;
    }

    /**
     * Limit the number of records returned
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the most recent value from the audit history
     */
    public function latest()
    {
        return $this->audit()->limit(1)->value();
    }

    /**
     * Get the historical values
     */
    public function all(): Collection
    {
        if (! $this->isAuditMode) {
            return collect([$this->current()]);
        }

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
            return $audit->value_new;
        });
    }

    /**
     * Get the first value in the result set
     */
    public function value()
    {
        return $this->all()->first();
    }

    /**
     * Get all values as a timeline with timestamps
     */
    public function timeline(): Collection
    {
        if (! $this->isAuditMode) {
            return collect([
                [
                    'value' => $this->current(),
                    'timestamp' => $this->model->modelAttributes->synced_at ?? now(),
                ],
            ]);
        }

        $query = $this->model->modelAttributeAudits()
            ->where('attribute', $this->attribute)
            ->orderBy('created_at', 'desc');

        if ($this->startDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        if ($this->limit) {
            $query->limit($this->limit);
        }

        return $query->get()->map(function ($audit) {
            return [
                'value' => $audit->value_new,
                'old_value' => $audit->value_old,
                'timestamp' => $audit->created_at,
                'change' => $audit->change_type,
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
