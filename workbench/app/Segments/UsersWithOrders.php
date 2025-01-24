<?php

namespace Workbench\App\Segments;

use StickleApp\Core\Attributes\SegmentName;
use StickleApp\\Core\Core\Attributes\SegmentRefreshInterval;
use StickleApp\\Core\Core\Contracts\Segment;
use StickleApp\\Core\Core\Filters\Base as Filter;
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
