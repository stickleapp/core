---
outline: deep
---

# Tracking Segments

Once you create a segment class, Stickle will begin tracking:

-   Which Models are currently contained within that segment;
-   A history of when Models enter and leave the segment;
-   Aggregate statistics about the models in the segment (over time).

## Specifying the Segment Refresh Frequency

Each segment is refreshed on an interval (in minutes) specified using the `schedule.exportSegments` setting in the `config/stickle.php` file.

This interval can be overriden on a per-segment basis using the `SegmentRefreshInterval` attribute of the class definition.

```php
use StickleApp\Core\Attributes\SegmentRefreshInterval;

#[SegmentRefreshInterval(30)]
class DailyActiveUsers extends Segment {}
```

## Specifying the Segment Statistics Refresh Frequency

The aggregated statistics for each segment are refreshed on an interval specifiec using the `schedule.recordSegmentStatistics` setting in the `config/stickle.php` file. Every 6 to 12 hours (360 or 720 minutes) should be sufficient.
