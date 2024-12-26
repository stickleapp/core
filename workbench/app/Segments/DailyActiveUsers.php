<?php

namespace Workbench\App\Segments;

use Dclaysmith\LaravelCascade\Contracts\Segment;
use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Illuminate\Database\Eloquent\Builder;
use Workbench\App\Models\User;

class DailyActiveUsers extends Segment
{
    public string $name = 'Daily Active Users';

    public int $exportInterval = 60 * 6; // every 6 hours

    public string $model = User::class;

    public function toBuilder(): Builder
    {
        // return User::cascade(
        //     Filter::eventCount('*')
        //         ->greaterThan(0)
        //         ->since(now()->subDays(7))
        // );

        // return $this->model::cascade(
        //     Filter::eventCount('clicked:something')
        //         ->greaterThan(0)
        //         ->startDate(now()->subDays(7))
        // );

        return $this->model::where('id', '>', 300);
    }
}
