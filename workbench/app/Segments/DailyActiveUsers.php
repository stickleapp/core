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

        return $this->model::stickleWhere(
            Filter::eventCount(event: 'clicked:something')
                ->sum()
                ->greaterThan(0)
                ->betweenDates(startDate: now()->subDays(7), endDate: now())
        )->stickleWhere(
            Filter::eventCount(event: 'clicked:something')
                ->avg()
                ->increased()
                ->greaterThan(0)
                ->betweenDateRanges(
                    compareToDateRange: [now()->subDays(14)->startOfDay(),
                        now()->subDays(7)->endOfDay()],
                    currentDateRange: [now()->subDays(7)->startOfDay(),
                        now()->endOfDay()]
                )
        );
    }
}
