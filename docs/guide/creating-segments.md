---
outline: deep
---

# Creating Segments

You create a segment by extending the `Dclaysmith\LaravelCascade\Contracts\Segment` class.

```php
namespace App\Segments;

use Dclaysmith\LaravelCascade\Attributes\SegmentExportInterval;
use Dclaysmith\LaravelCascade\Attributes\SegmentName;
use Dclaysmith\LaravelCascade\Contracts\Segment;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

#[SegmentName('Active Users')]
#[SegmentExportInterval(30)]
class ActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::where('status', 'active');
    }
}
```

There are two items in the class you must extend: the `$model` attribute and `toBuilder()` function.

`$model` must be an class that implements the `trackable` trait. The segment will contain objects of this class.

The `toBuilder()` method must return an instance of a `Illuminate\Database\Eloquent\Builder` class.

## Attributes

You can optionally provide values for two custom class attributes:

`SegmentName` is a human-readable name you may provide for this segment. In the absense of this attribute, Stitch will infer the name of the segment from the name of the class.

`SegmentExportInterval` allows you specify a length of time (in minutes) that should elapse between requerying this segment. This will override the default set in `config('stickle.schedule.ExportSegments')`.
