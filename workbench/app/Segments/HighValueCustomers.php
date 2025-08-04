<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\Customer;

#[StickleSegmentMetadata([
    'exportInterval' => 3600,
    'name' => 'High Value Customers',
    'description' => 'The users with a ACV in the  top 10%.',
])]
class HighValueCustomers extends SegmentContract
{
    public string $model = Customer::class;

    public function toBuilder(): Builder
    {

        // return $this->model::stickleWhere(
        //     Filter::eventCount('clicked:something')
        //         ->greaterThan(0)
        //         ->startDate(now()->subDays(7))
        // );

        return $this->model::query();
    }
}
