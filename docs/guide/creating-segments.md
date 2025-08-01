---
outline: deep
---

# Creating Segments

You create a segment by extending the `StickleApp\Core\Contracts\Segment` class.

```php
namespace App\Segments;

use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\SegmentContract;
use StickleApp\Core\Filters\Base as Filter;
use App\Models\User;

#[StickleSegmentMetadata([
    'exportInterval' => 3600,
    'name' => 'Currently Active Users',
    'description' => 'All of the customers who have logged in within 7 days.',
])]
class ActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::where('status', 'active')
            ->stickleWhere(
                Filter::sessionCount(now()->subDays(7))
                    ->greaterThan(0)
            );
    }
}
```

There are two items in the class you must extend: the `$model` attribute and `toBuilder()` function.

`$model` must be an class that implements the `StickleEntity` trait. The segment will contain objects of this class.

The `toBuilder()` method must return an instance of a `Illuminate\Database\Eloquent\Builder` class.

## Attributes

You can optionally provide values for two custom class attributes:

`SegmentName` is a human-readable name you may provide for this segment. In the absense of this attribute, Stitch will infer the name of the segment from the name of the class.

`SegmentExportInterval` allows you specify a length of time (in minutes) that should elapse between requerying this segment. This will override the default set in `config('stickle.schedule.exportSegments')`.
