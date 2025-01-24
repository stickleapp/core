<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use StickleApp\Core\Contracts\FilterTarget;
use StickleApp\Core\Contracts\FilterTest;

class Contains extends FilterTest
{
    public function __construct(public string $comparator, public bool $caseSensitive = false) {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(DB::raw(sprintf('(%s)', $target->property())), 'ilike', sprintf('%%%s%%', $this->comparator));
    }
}
