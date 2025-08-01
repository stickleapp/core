---
outline: deep
---

# Aggregating User Attributes

Stickle allows you to track individuals (`Users`) as well as organizations (`Groups`). It is often useful to see and filter on the aggregate of `User` metrics at the `Group` level.

For instance, if your software manages retail stores. You may have individual employees (`Users`) working at specific retail locations (`Groups`). Perhaps each employee has a `user_rating` provided by customers. Stickle allows you to see the `AVG`, `MIN` and `MAX` `user_rating` for employees at each retail location `Group`.

```php
use App\Models\Group; // The Group model has the `StickleEntity` trait

$userRating = Group::find(id: 34)->stickleUserAggregate(attribute: 'user_rating', aggregate: 'MAX', include_children: false);
```

## Prerequisites

To avail of this functionality you need to add the `StickleEntity` trait on

# Numeric Attributes

# Text Attributes

# What about parent <> child relationships?

Stickle supports parent <> child relationships and will _roll up_ statistics from child objects into a parent object.

## User Attributes Rolled up to a Group / Parent Group

A user may have an attribute such as "user_rating" (1-5 stars). Stickle is able to roll this attribute up to the Group Level, providing:

-   The number of users with a rating attribute (count);
-   The average rating;
-   The sum total of the rating (not very useful);
-   The lowest rating; and
-   The highest rating;

```sql
SELECT
    groups.id
    CONCAT('user__',object_attributes.attribute_name),
    SUM(object_attributes.attribute_name)
FROM
    users
JOIN
    object_attributes
ON
    object_attributes.object_oid = users.id
JOIN
    groups
ON
    groups.id = users.group_id
GROUP BY
    groups.id
UNION
SELECT
    parent.id
    CONCAT('user__',object_attributes.attribute_name),
    SUM(object_attributes.attribute_name)
FROM
    users
JOIN
    object_attributes
ON
    object_attributes.object_oid = users.id
JOIN
    groups
ON
    groups.id = users.group_id
JOIN
    groups AS parent
ON
    groups.parent_id = parent.id
GROUP BY
    parent.id
```

## Group Attributes Rolled up to a Parent Group

```sql
SELECT
    parent.id
    CONCAT('group__',object_attributes.attribute_name),
    SUM(object_attributes.attribute_name)
FROM
    groups
JOIN
    object_attributes
ON
    object_attributes.object_oid = users.id
JOIN
    groups AS parent
ON
    groups.parent_id = parent.id
GROUP BY
    parent.id
```
