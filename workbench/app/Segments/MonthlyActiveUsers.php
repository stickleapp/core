<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[StickleSegmentMetadata([
    'refreshInterval' => 30,
    'name' => 'Monthly Active Users (MAU)',
    'description' => 'Monthly active users.',
])]
class MonthlyActiveUsers extends SegmentContract
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        // return $this->model::stickle(
        //     Filter::eventCount('clicked:something')
        //         ->greaterThan(0)
        //         ->startDate(now()->subDays(30))
        // );

        return $this->model::where('id', '>', 500);
    }
}
