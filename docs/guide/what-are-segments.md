---
outline: deep
---

# What are Segments?

A segment is a group of your `StickleEntity` models matching specific criteria. Examples include:

-   Who are your "active" users?
-   Who are your "inactive" users?
-   Which users are "slipping away"?
-   Who are your "high value" customers?
-   Who are your "primary contacts"?

You define segment "in-code" by creating a class that extends the `StickleApp\Core\Contracts\Segment` class.

## Segment History

Once you have defined a segment, Trickle will automatically update which models belong in the Segment and maintain a historical record as models enter and leave the segment.

## Segment Statistics

Additionally, Stickle calculates aggregates of each of your model-level tracked attributes and tracks these over time for each segment. For example, if you define a `mrr` attribute and add it to your class's `stickleTrackedAttributes()` method, each Segment will track the aggregate values (AVG, MAX, MIN, SUM) of `mrr` in that segment.

## Filtering by existence in Segments

Stickle provides custom Eloquent methods so you can filter users based on whether or not are in (or have ever been in) a segment. These filters include:

-   InSegment
-   HasBeenInSegment
-   NotInSegment
-   NeverBeenInSegment
