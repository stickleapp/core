<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Attributes: Name / Refresh Every / Description */
abstract class Segment
{
    public string $name;

    public int $exportInterval;

    public string $model;

    /**
     * @return Builder<Model>
     */
    abstract public function toBuilder(): Builder;
}
