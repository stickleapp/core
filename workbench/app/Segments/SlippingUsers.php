<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[StickleSegmentMetadata([
    'exportInterval' => 30,
    'name' => 'Slipping Users',
    'description' => 'Users with declining usage.',
])]
class SlippingUsers extends SegmentContract
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        // return $this->model::stickle(
        //     Filter::eventCount('clicked:something')
        //         ->decreased(
        //             [now()->subWeeks(4), now()->subWeeks(2)],
        //             [now()->subWeeks(2), now()],
        //         )
        //         ->greaterThan(40) // meaningless -- make percentage
        // );

        return $this->model::where('id', '>', 200);
    }
}
