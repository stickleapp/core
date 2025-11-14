---
outline: deep
---

# Filters and Eloquent Methods

Stickle adds powerful filter options to Eloquent through two scopes: `stickleWhere()` and `stickleOrWhere()`, which behave like `where()` and `orWhere()` but enable filtering by user behavior, events, and tracked attributes.

These scopes accept a single Filter object (`StickleApp\Core\Filters\Base`) that defines a filter which will be applied to your query builder.

## Fluent Interface

The supplied filter can be configured by calling fluent methods which return the instance itself, allowing you to chain methods together to define your filter.

An example:

```php
$users = User::query()
    ->stickleWhere(
        Filter::eventCount(event: 'clicked:something')
            ->avg()
            ->moreThan(5)
            ->betweenDates(
                startDate: now()->subDays(14),
                endDate: now()->subDays(7)
            )
    )->stickleOrWhere(
        Filter::sessions()
            ->sum()
            ->increased()
            ->moreThan(5)
            ->betweenDateRanges(
                compareToDateRange: [now()->subDays(14), now()->subDays(7)],
                currentDateRRange: [now()->subDays(7), now()]
            )
    )->get();
```

## Filter Types

There are several different types of filters you can pass in to the `stickle` and `orStickle` scopes.

| Filter       | Description                                                                  |
| ------------ | :--------------------------------------------------------------------------- |
| Boolean      | Filter by boolean attributes stored in model JSON data                       |
| Date         | Filter by date attributes stored in model JSON data                          |
| Datetime     | Filter by datetime attributes stored in model JSON data                      |
| EventCount   | Filter by event counts with aggregation and date range support               |
| Number       | Filter by numeric attributes with aggregation, delta, and date range support |
| RequestCount | Filter by HTTP request counts with aggregation and date range support        |
| SessionCount | Filter by session counts with aggregation and date range support             |
| Text         | Filter by text attributes stored in model JSON data                          |

### Boolean

Boolean filters test boolean attributes stored in the model's JSON data field.

**Available Tests:**

-   `isTrue()` - Tests if the boolean attribute is true
-   `isFalse()` - Tests if the boolean attribute is false
-   `isNotTrue()` - Tests if the boolean attribute is not true (false or null)
-   `isNotFalse()` - Tests if the boolean attribute is not false (true or null)
-   `isNull()` - Tests if the boolean attribute is null
-   `isNotNull()` - Tests if the boolean attribute is not null

```php
// Users where 'email_verified' is true
$users = User::stickleWhere(Filter::boolean('email_verified')->isTrue())->get();

// Users where 'is_premium' is false
$users = User::stickleWhere(Filter::boolean('is_premium')->isFalse())->get();

// Users where 'newsletter_subscribed' is not null
$users = User::stickleWhere(Filter::boolean('newsletter_subscribed')->isNotNull())->get();
```

### Date

Date filters test date attributes stored in the model's JSON data field.

**Available Tests:**

-   `equals(date)` - Tests if the date equals the given value
-   `isBefore(date)` - Tests if the date is before the given value
-   `isAfter(date)` - Tests if the date is after the given value
-   `occurredBefore(date)` - Alias for `isBefore()`
-   `occurredAfter(date)` - Alias for `isAfter()`
-   `willOccurBefore(date)` - Tests if the date will occur before the given value
-   `willOccurAfter(date)` - Tests if the date will occur after the given value
-   `between(startDate, endDate)` - Tests if the date is between two values

```php
// Users where 'birth_date' is after 1990-01-01
$users = User::stickleWhere(Filter::date('birth_date')->isAfter('1990-01-01'))->get();

// Users where 'trial_expires' is before today
$users = User::stickleWhere(Filter::date('trial_expires')->isBefore(now()))->get();

// Users born between 1980 and 1990
$users = User::stickleWhere(Filter::date('birth_date')->between('1980-01-01', '1990-12-31'))->get();
```

### Datetime

Datetime filters test datetime attributes stored in the model's JSON data field.

**Available Tests:**

-   `equals(datetime)` - Tests if the datetime equals the given value
-   `isBefore(datetime)` - Tests if the datetime is before the given value
-   `isAfter(datetime)` - Tests if the datetime is after the given value
-   `occurredBefore(datetime)` - Alias for `isBefore()`
-   `occurredAfter(datetime)` - Alias for `isAfter()`
-   `willOccurBefore(datetime)` - Tests if the datetime will occur before the given value
-   `willOccurAfter(datetime)` - Tests if the datetime will occur after the given value
-   `between(startDatetime, endDatetime)` - Tests if the datetime is between two values

```php
// Users where 'last_login' is after yesterday
$users = User::stickleWhere(Filter::datetime('last_login')->isAfter(now()->subDay()))->get();

// Users where 'created_at' is before last week
$users = User::stickleWhere(Filter::datetime('created_at')->isBefore(now()->subWeek()))->get();

// Users active in the last hour
$users = User::stickleWhere(
    Filter::datetime('last_activity')->between(now()->subHour(), now())
)->get();
```

### EventCount

EventCount filters aggregate event data over specified time periods. All EventCount filters require an aggregate method (`sum()`, `avg()`, `min()`, `max()`, `count()`) and date range.

**Required Methods:**

-   Aggregate: `sum()`, `avg()`, `min()`, `max()`, `count()`
-   Date range: `betweenDates(startDate, endDate)` or `betweenDateRanges(compareRange, currentRange)`

**Available Tests:**

-   `equals(value)` - Tests if the aggregated value equals the given value
-   `greaterThan(value)` - Tests if the aggregated value is greater than the given value
-   `lessThan(value)` - Tests if the aggregated value is less than the given value
-   `greaterThanOrEqualTo(value)` - Tests if the aggregated value is greater than or equal to the given value
-   `lessThanOrEqualTo(value)` - Tests if the aggregated value is less than or equal to the given value
-   `between(min, max)` - Tests if the aggregated value is between two values

```php
// Users who clicked 'buy_button' more than 5 times in the last 30 days
$users = User::stickleWhere(
    Filter::eventCount('clicked:buy_button')
        ->count()
        ->betweenDates(startDate: now()->subDays(30), endDate: now())
        ->greaterThan(5)
)->get();

// Users with average session duration over 10 minutes last week
$users = User::stickleWhere(
    Filter::eventCount('session:duration')
        ->avg()
        ->betweenDates(startDate: now()->subWeek(), endDate: now())
        ->greaterThan(10)
)->get();

// Users whose click count increased compared to previous period
$users = User::stickleWhere(
    Filter::eventCount('clicked:product')
        ->sum()
        ->increased()
        ->betweenDateRanges(
            compareToDateRange: [now()->subDays(14), now()->subDays(7)],
            currentDateRange: [now()->subDays(7), now()]
        )
        ->greaterThan(0)
)->get();
```

### Number

Number filters test numeric attributes stored in the model's JSON data field. They support simple value comparisons, aggregations over time periods, and delta comparisons between periods.

**Available Aggregates:**

-   `avg()` - Average value over time period
-   `sum()` - Sum of values over time period
-   `min()` - Minimum value over time period
-   `max()` - Maximum value over time period
-   `count()` - Count of values over time period

**Available Delta Methods:**

-   `increased()` - Value increased between periods
-   `decreased()` - Value decreased between periods
-   `changed()` - Value changed between periods

**Available Tests:**

-   `equals(value)` - Tests if the value equals the given value
-   `greaterThan(value)` - Tests if the value is greater than the given value
-   `lessThan(value)` - Tests if the value is less than the given value
-   `greaterThanOrEqualTo(value)` - Tests if the value is greater than or equal to the given value
-   `lessThanOrEqualTo(value)` - Tests if the value is less than or equal to the given value
-   `between(min, max)` - Tests if the value is between two values

```php
// Simple value comparison - users with score greater than 100
$users = User::stickleWhere(Filter::number('score')->greaterThan(100))->get();

// Users with score between 50 and 150
$users = User::stickleWhere(Filter::number('score')->between(50, 150))->get();

// Users whose average purchase amount in last 30 days is over $500
$users = User::stickleWhere(
    Filter::number('purchase_amount')
        ->avg()
        ->betweenDates(startDate: now()->subDays(30), endDate: now())
        ->greaterThan(500)
)->get();

// Users whose total spending increased by more than $100 compared to previous month
$users = User::stickleWhere(
    Filter::number('purchase_amount')
        ->sum()
        ->increased()
        ->betweenDateRanges(
            compareToDateRange: [now()->subMonths(2), now()->subMonth()],
            currentDateRange: [now()->subMonth(), now()]
        )
        ->greaterThan(100)
)->get();

// Users whose maximum single purchase decreased compared to last quarter
$users = User::stickleWhere(
    Filter::number('purchase_amount')
        ->max()
        ->decreased()
        ->betweenDateRanges(
            compareToDateRange: [now()->subMonths(6), now()->subMonths(3)],
            currentDateRange: [now()->subMonths(3), now()]
        )
        ->greaterThan(0)
)->get();
```

### RequestCount

RequestCount filters aggregate HTTP request data for specific URLs over time periods. All RequestCount filters require an aggregate method and date range.

**Required Methods:**

-   Aggregate: `sum()`, `avg()`, `min()`, `max()`, `count()`
-   Date range: `betweenDates(startDate, endDate)` or `betweenDateRanges(compareToDateRange, currentDateRange)`

**Available Tests:**

-   `equals(value)` - Tests if the aggregated value equals the given value
-   `greaterThan(value)` - Tests if the aggregated value is greater than the given value
-   `lessThan(value)` - Tests if the aggregated value is less than the given value
-   `greaterThanOrEqualTo(value)` - Tests if the aggregated value is greater than or equal to the given value
-   `lessThanOrEqualTo(value)` - Tests if the aggregated value is less than or equal to the given value
-   `between(min, max)` - Tests if the aggregated value is between two values

```php
// Users who made more than 10 API requests in the last 7 days
$users = User::stickleWhere(
    Filter::requestCount('/api/data')
        ->count()
        ->betweenDates(startDate: now()->subDays(7), endDate: now())
        ->greaterThan(10)
)->get();

// Users whose dashboard visits increased compared to last week
$users = User::stickleWhere(
    Filter::requestCount('/dashboard')
        ->sum()
        ->increased()
        ->betweenDateRanges(
            compareToDateRange: [now()->subDays(14), now()->subDays(7)],
            currentDateRange: [now()->subDays(7), now()]
        )
        ->greaterThan(0)
)->get();
```

### SessionCount

SessionCount filters aggregate session data over time periods. All SessionCount filters require an aggregate method and date range.

**Required Methods:**

-   Aggregate: `sum()`, `avg()`, `min()`, `max()`, `count()`
-   Date range: `betweenDates(startDate, endDate)` or `betweenDateRanges(compareToDateRange, currentDateRange)`

**Available Tests:**

-   `equals(value)` - Tests if the aggregated value equals the given value
-   `greaterThan(value)` - Tests if the aggregated value is greater than the given value
-   `lessThan(value)` - Tests if the aggregated value is less than the given value
-   `greaterThanOrEqualTo(value)` - Tests if the aggregated value is greater than or equal to the given value
-   `lessThanOrEqualTo(value)` - Tests if the aggregated value is less than or equal to the given value
-   `between(min, max)` - Tests if the aggregated value is between two values

```php
// Users with more than 5 sessions in the last 30 days
$users = User::stickleWhere(
    Filter::sessionCount()
        ->count()
        ->betweenDates(startDate: now()->subDays(30), endDate: now())
        ->greaterThan(5)
)->get();

// Users whose session count increased compared to previous month
$users = User::stickleWhere(
    Filter::sessionCount()
        ->sum()
        ->increased()
        ->betweenDateRanges(
            compareToDateRange: [now()->subMonths(2), now()->subMonth()],
            currentDateRange: [now()->subMonth(), now()]
        )
        ->greaterThan(0)
)->get();
```

### Text

Text filters test text attributes stored in the model's JSON data field.

**Available Tests:**

-   `equals(text)` - Tests if the text equals the given value
-   `contains(text)` - Tests if the text contains the given substring
-   `beginsWith(text)` - Tests if the text begins with the given substring

```php
// Users where 'company' equals 'Acme Corp'
$users = User::stickleWhere(Filter::text('company')->equals('Acme Corp'))->get();

// Users where 'bio' contains 'developer'
$users = User::stickleWhere(Filter::text('bio')->contains('developer'))->get();

// Users where 'job_title' begins with 'Senior'
$users = User::stickleWhere(Filter::text('job_title')->beginsWith('Senior'))->get();
```

## Creating Custom Scopes

While you can use filters directly in your queries, creating custom scopes makes your code more readable and maintainable. Laravel's local scopes are perfect for encapsulating common Stickle filters.

### Basic Custom Scopes

Create scopes using the `Filter` facade:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Filters\Filter;

class User extends Model
{
    /**
     * Scope a query to only include users active in the last 7 days
     */
    public function scopeActive(Builder $query): void
    {
        $query->stickleWhere(
            Filter::requestCount('/dashboard')
                ->count()
                ->betweenDates(startDate: now()->subDays(7), endDate: now())
                ->greaterThan(0)
        );
    }

    /**
     * Scope a query to include high-value users (purchase amount > $1000)
     */
    public function scopeHighValue(Builder $query): void
    {
        $query->stickleWhere(
            Filter::number('purchase_amount')
                ->sum()
                ->betweenDates(startDate: now()->subYear(), endDate: now())
                ->greaterThan(1000)
        );
    }

    /**
     * Scope a query to include users with growing engagement
     */
    public function scopeGrowingEngagement(Builder $query): void
    {
        $query->stickleWhere(
            Filter::eventCount('page:view')
                ->count()
                ->increased()
                ->betweenDateRanges(
                    compareToDateRange: [now()->subMonths(2), now()->subMonth()],
                    currentDateRange: [now()->subMonth(), now()]
                )
                ->greaterThan(5)
        );
    }

    /**
     * Scope a query to include premium subscribers
     */
    public function scopePremium(Builder $query): void
    {
        $query->stickleWhere(
            Filter::boolean('is_premium')->isTrue()
        );
    }
}
```

### Complex Scopes with Multiple Filters

Combine multiple filters using `stickleWhere()` and `stickleOrWhere()`:

```php
/**
 * Scope for users likely to churn (low activity + no recent purchases)
 */
public function scopeLikelyToChurn(Builder $query): void
{
    $query->stickleWhere(
        // Low session activity in the last 30 days
        Filter::sessionCount()
            ->count()
            ->betweenDates(startDate: now()->subDays(30), endDate: now())
            ->lessThan(3)
    )->stickleWhere(
        // No purchases in the last 60 days
        Filter::eventCount('purchase:completed')
            ->count()
            ->betweenDates(startDate: now()->subDays(60), endDate: now())
            ->equals(0)
    );
}

/**
 * Scope for power users (high engagement OR high value)
 */
public function scopePowerUsers(Builder $query): void
{
    $query->stickleWhere(
        // High event activity
        Filter::eventCount('button:click')
            ->count()
            ->betweenDates(startDate: now()->subDays(30), endDate: now())
            ->greaterThan(100)
    )->stickleOrWhere(
        // OR high purchase value
        Filter::number('purchase_amount')
            ->sum()
            ->betweenDates(startDate: now()->subDays(30), endDate: now())
            ->greaterThan(500)
    );
}
```

### Segment-Based Scopes

For performance-critical applications, create scopes that filter by pre-computed segments:

```php
/**
 * Scope a query to only return users currently in the 'ActiveUsers' segment
 */
public function scopeActive(Builder $query): void
{
    $query->stickleWhere(
        Filter::segment('ActiveUsers')->isInSegment()
    );
}

/**
 * Scope for users in multiple segments
 */
public function scopeEngagedCustomers(Builder $query): void
{
    $query->stickleWhere(
        Filter::segment('HighEngagement')->isInSegment()
    )->stickleWhere(
        Filter::segment('RecentPurchasers')->isInSegment()
    );
}
```

::: warning Performance Consideration
Segment-based filters use pre-calculated data and may be slightly stale (segments are updated periodically). Use direct filters when real-time accuracy is essential.
:::

### Using Custom Scopes

Once defined, use scopes naturally in your queries:

```php
use App\Models\User;

// Single scope
$activeUsers = User::active()->get();

// Multiple scopes
$powerUsers = User::active()->highValue()->get();

// Combining with regular Eloquent methods
$recentPowerUsers = User::active()
    ->highValue()
    ->where('created_at', '>', now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->paginate(25);

// Complex combinations
$targetUsers = User::active()
    ->orWhere(function ($query) {
        $query->growingEngagement()->premium();
    })
    ->get();
```

## Performance Tips

1. **Use segments for frequently queried filters** - Pre-compute expensive filters as segments
2. **Limit date ranges** - Smaller date ranges perform better than large historical queries
3. **Index your model attributes** - Ensure tracked attributes are properly indexed
4. **Combine filters efficiently** - Use `stickleWhere()` for AND conditions and `stickleOrWhere()` for OR conditions

## Next Steps

Now that you understand filters:

- **[Customer Segments](/guide/segments)** - Build segments using filters
- **[Tracking Attributes](/guide/tracking-attributes)** - Define attributes to filter on
- **[Filter Reference](/guide/filter-reference)** - Quick reference of all filter methods
- **[Recipes](/guide/recipes)** - Real-world filter examples
