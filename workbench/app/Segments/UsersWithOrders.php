<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[StickleSegmentMetadata([
    'exportInterval' => 30,
    'name' => 'Uses with Orders',
    'description' => 'Uses who have placed at least 1 order.',
])]
class UsersWithOrders extends SegmentContract
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        // return $this->model::stickleWhere(
        //     Filter::number('order_count')
        //         ->greaterThan(2)
        // );

        return $this->model::where('id', '>', 500);
    }
}
