<?php

namespace Workbench\App\Segments;

use Dclaysmith\LaravelCascade\Contracts\Segment;
use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;
use Workbench\App\Models\User;

class MonthlyActiveUsers extends Segment
{
    public string $name = 'Daily Active Users';

    public int $exportInterval = 60 * 6; // every 6 hours

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
