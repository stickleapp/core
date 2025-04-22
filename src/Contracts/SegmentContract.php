<?php

declare(strict_types=1);

namespace StickleApp\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class SegmentContract
{
    public string $model;

    /**
     * @return Builder<Model>
     */
    abstract public function toBuilder(): Builder;
}
