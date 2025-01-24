<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\SegmentName;
use StickleApp\Core\Attributes\SegmentRefreshInterval;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[SegmentName('Daily Active Users')]
#[SegmentRefreshInterval(30)]
class DailyActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {

        // return $this->model::cascade(
        //     Filter::eventCount('clicked:something')
        //         ->greaterThan(0)
        //         ->startDate(now()->subDays(7))
        // );

        return $this->model::where('id', '>', 300);
    }
}
