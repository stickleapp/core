<?php

namespace Workbench\App\Segments;

use Dclaysmith\LaravelCascade\Attributes\SegmentName;
use Dclaysmith\LaravelCascade\Attributes\SegmentRefreshInterval;
use Dclaysmith\LaravelCascade\Contracts\Segment;
use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;
use Workbench\App\Models\User;

#[SegmentName('Users with Orders')]
#[SegmentRefreshInterval(30)]
class UsersWithOrders extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::cascade(
            Filter::number('order_count')
                ->greaterThan(2)
        );
    }
}
