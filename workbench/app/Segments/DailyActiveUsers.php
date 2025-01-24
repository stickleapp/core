<?php

namespace Workbench\App\Segments;

use StickleApp\Core\Attributes\SegmentName;
use StickleApp\\Core\Core\Attributes\SegmentRefreshInterval;
use StickleApp\\Core\Core\Contracts\Segment;
use StickleApp\\Core\Core\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;
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
