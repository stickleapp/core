---
outline: deep
---

# Stickle Eloquent Methods

Stickle adds additional filter options to Eloquent through two Eloquent Scopes `stickle()` and `orStickle()` which behave in the same way as `where()` and `orWhere()` respectively.

These scopes accept a single `Dclaysmith\LaravelCascade\Filters\Base` that defines a filter which will be applied to your query builder.

## Fluent Interface

`Dclaysmith\LaravelCascade\Filters\Base` can be configured by calling fluent methods which return the instance itself, allowing you to chain methods together to define your filter.

```php
use App\Models\User;
use Dclaysmith\LaravelCascade\Filters\Base as Filter;
use Dclaysmith\LaravelCascade\Filters\Targets\EventCount;

$users = User::stickle(
        new EventCount()
            ->event('clicked:button')
            ->occurrences(">", 0)
            ->since(now()->subDays(7));
    )->orStickle(
        new EventCount()
            ->event('submitted:form')
            ->occurrences(">=", 10)
            ->between(now()->subDays(16), now()->subDays(5));
    )->get();
```

## Filter Types

There are several different types of filters you can pass in to the `stickle` and `orStickle`.

| Filter            | Description |
| ----------------- | :---------- |
| Boolean           |             |
| Date              |             |
| Datetime          |             |
| EventCount        |             |
| EventCountDelta   |             |
| Number            |             |
| RequestCount      |             |
| RequestCountDelta |             |
| Segment           |             |
| Text              |             |

### Boolean

### Date

### Datetime

### EventCount

### EventCountDelta

### Number

### RequestCount

### RequestCountDelta

### Segment

### Text
