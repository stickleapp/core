<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\SegmentName;
use StickleApp\Core\Attributes\SegmentRefreshInterval;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[SegmentName('Monthly Active Users')]
#[SegmentRefreshInterval(30)]
class MonthlyActiveUsers extends Segment
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
