<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Contracts;

use Illuminate\Database\Eloquent\Builder;

/** Attributes: Name / Refresh Every / Description */
abstract class Segment
{
    public $name;

    public $refreshInterval;

    public $class; // could we determine from builder returned by export?

    abstract public function toBuilder(): Builder;
}
