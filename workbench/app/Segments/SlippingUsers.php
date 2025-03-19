<?php

namespace Workbench\App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\SegmentName;
use StickleApp\Core\Attributes\SegmentRefreshInterval;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use Workbench\App\Models\User;

#[SegmentName('Slipping Users')]
#[SegmentRefreshInterval(30)]
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
