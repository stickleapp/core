<?php

namespace Workbench\App\Segments;

use Dclaysmith\LaravelCascade\Contracts\Segment;
use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;
use Workbench\App\Models\User;

class UsersWithOrders extends Segment
{
    public string $name = 'Users with at least one order';

    public int $exportInterval = 60 * 6; // every 6 hours

    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::cascade(
            Filter::number('order_count')
                ->greaterThan(2)
        );
    }
}
