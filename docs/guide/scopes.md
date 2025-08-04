---
outline: deep
---

# Creating Custom Scopes

Stickle extends Eloquent so you can query your usage data in a fluent manner. However, it can be repetitive and error prone to repeat these queries. `Local` scopes are a great way to prevent repeating yourself.

## Using the Fluent Filter API

With Stickle's fluent filter API, you can create readable and maintainable scopes using the `Filter` facade:

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

## Complex Scopes with Multiple Filters

You can combine multiple filters using `stickle()` and `stickleOrWhere()` for complex business logic:

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

## Segment-Based Scopes

For performance-critical applications, you can create scopes that filter by pre-computed segments:

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
Using segment-based filters can return stale results (segments are updated periodically). If real-time accuracy is essential, use the direct filter methods instead.
:::

## Date-Based Scopes

Create scopes for different time periods using date filters:

```php
/**
 * Users who signed up this month
 */
public function scopeNewThisMonth(Builder $query): void
{
    $query->stickleWhere(
        Filter::date('signup_date')
            ->isAfter(now()->startOfMonth())
    );
}

/**
 * Users with recent login activity
 */
public function scopeRecentlyActive(Builder $query): void
{
    $query->stickleWhere(
        Filter::datetime('last_login')
            ->isAfter(now()->subHours(24))
    );
}

/**
 * Users born in the 1990s
 */
public function scopeMillennials(Builder $query): void
{
    $query->stickleWhere(
        Filter::date('birth_date')
            ->between('1990-01-01', '1999-12-31')
    );
}
```

## Using Custom Scopes

Once defined, you can use these scopes naturally in your Eloquent queries:

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
4. **Combine filters efficiently** - Use `stickle()` for AND conditions and `stickleOrWhere()` for OR conditions

## Available Filter Types

All filter types from the [Eloquent Methods](/guide/eloquent-methods) documentation can be used in scopes:

-   **Boolean** - `Filter::boolean('attribute')`
-   **Date** - `Filter::date('attribute')`
-   **Datetime** - `Filter::datetime('attribute')`
-   **EventCount** - `Filter::eventCount('event_name')`
-   **Number** - `Filter::number('attribute')`
-   **RequestCount** - `Filter::requestCount('url')`
-   **SessionCount** - `Filter::sessionCount()`
-   **Text** - `Filter::text('attribute')`

Read more about local scopes in the [Laravel documentation](https://laravel.com/docs/11.x/eloquent#local-scopes).
