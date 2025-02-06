<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\SegmentName;
use StickleApp\Core\Attributes\SegmentRefreshInterval;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[SegmentName('Users with Orders')]
#[SegmentRefreshInterval(30)]
class UsersWithOrders extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::stickle(
            Filter::number('order_count')
                ->greaterThan(2)
        );
    }
}
