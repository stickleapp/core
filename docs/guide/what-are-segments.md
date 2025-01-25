---
outline: deep
---

# What are Segments?

A segment is a filtered set of your Users (or any class with the `trackable` trait). Examples include:

-   Who are your "active" users?
-   Who are your "inactive" users?
-   Which users are "slipping away"?
-   Who are your "high value" customers?
-   Who are your "primary contacts"?

You define segment "in-code" by creating a class that extends the `StickleApp\Core\Contracts\Segment` class.

Once you have defined a segment, Trickle will automatically update which Users belong in the Segment and maintain a historical record as Users enter and leave the segment.

Additionally, Stickle calculates aggregates of each of your model-level `$observedAttributes` and tracks these over time.

Stickle provides custom Eloquent methods so you can filter users based on whether or not are in (or have ever been in) a segment.
