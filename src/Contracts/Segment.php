<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

use Illuminate\Database\Eloquent\Builder;

/** Attributes: Name / Refresh Every / Description */
abstract class Segment
{
    public string $name;

    public int $refreshInterval;

    public string $class; // could we determine from builder returned by export?

    abstract public function toBuilder(): Builder;
}
