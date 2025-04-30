<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[StickleSegmentMetadata([
    'exportInterval' => 30,
    'name' => 'Daily Active Users (DAU)',
    'description' => 'Daily active users.',
])]
class DailyActiveUsers extends SegmentContract
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {

        // return $this->model::stickle(
        //     Filter::eventCount('clicked:something')
        //         ->greaterThan(0)
        //         ->startDate(now()->subDays(7))
        // );

        return $this->model::where('id', '>', 300);
    }
}
