<?php

declare(strict_types=1);

namespace StickleApp\Core\Filters\Tests;

use StickleApp\\Core\Core\Contracts\FilterTarget;
use StickleApp\\Core\Core\Contracts\FilterTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Contains extends FilterTest
{
    public function __construct(public string $comparator, public bool $caseSensitive = false) {}

    public function applyFilter(Builder $builder, FilterTarget $target, string $operator): Builder
    {
        return $builder->where(DB::raw(sprintf('(%s)', $target->property())), 'ilike', sprintf('%%%s%%', $this->comparator));
    }
}
