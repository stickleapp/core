<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\SegmentName;
use StickleApp\Core\Attributes\SegmentRefreshInterval;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\Customer;

#[SegmentName('High Value Customers')]
#[SegmentRefreshInterval(30)]
class HighValueCustomers extends SegmentContract
{
    public string $model = Customer::class;

    public function toBuilder(): Builder
    {

        // return $this->model::stickle(
        //     Filter::eventCount('clicked:something')
        //         ->greaterThan(0)
        //         ->startDate(now()->subDays(7))
        // );

        return $this->model::query();
    }
}
