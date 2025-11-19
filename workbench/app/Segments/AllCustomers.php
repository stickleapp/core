<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use Workbench\App\Models\Customer;

#[StickleSegmentMetadata([
    'exportInterval' => 3600,
    'name' => 'All Customers',
    'description' => 'All of the customers.',
])]
class AllCustomers extends SegmentContract
{
    public string $model = Customer::class;

    public function toBuilder(): Builder
    {
        return $this->model::query();
    }
}
