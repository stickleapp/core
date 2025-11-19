<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use Workbench\App\Models\User;

#[StickleSegmentMetadata([
    'exportInterval' => 30,
    'name' => 'All Users',
    'description' => 'The users.',
])]
class AllUsers extends SegmentContract
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query();
    }
}
