---
outline: deep
---

# Aggregating Group Attributes

Stickle allows you to create an audit history of your Users. To do so, simply add the `StickleApp\Core\Traits\Trackable` and add the attributes you want to track to the `$observedAttributes` array of your User class.

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
