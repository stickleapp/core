<?php

namespace Workbench\App\Segments;

use Dclaysmith\LaravelCascade\Attributes\SegmentName;
use Dclaysmith\LaravelCascade\Attributes\SegmentRefreshInterval;
use Dclaysmith\LaravelCascade\Contracts\Segment;
use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;
use Workbench\App\Models\User;

#[SegmentName('Monthly Active Users')]
#[SegmentRefreshInterval(30)]
class MonthlyActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        // return $this->model::cascade(
        //     Filter::eventCount('clicked:something')
        //         ->greaterThan(0)
        //         ->startDate(now()->subDays(30))
        // );

        return $this->model::where('id', '>', 500);
    }
}
