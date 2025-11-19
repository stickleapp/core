<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[StickleSegmentMetadata([
    'name' => 'Active Users',
    'description' => 'Users that have made a request in the last 7 days',
    'exportInterval' => 360, // Re-calculate every 6 hours
])]
class ActiveUsers extends SegmentContract
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->stickleWhere(
                Filter::requestCount()
                    ->count()
                    ->greaterThan(0)
                    ->betweenDates(
                        startDate: now()->subDays(7)->startOfDay(),
                        endDate: today(),
                    )
            );
    }
}
