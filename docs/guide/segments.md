---
outline: deep
---

# Customer Segments

Segments allow you to group customers based on specific criteria and track those groups over time. If you know Laravel Eloquent, you can build powerful segments using familiar syntax.

## What are Segments?

A segment is a group of your `StickleEntity` models matching specific criteria. Segments help you answer questions like:

- Who are your "active" users?
- Who are your "inactive" or "slipping away" users?
- Which customers are "high value"?
- Who are your "primary contacts"?
- Which accounts are "at risk of churning"?

You define segments "in-code" by creating a class that extends the `Segment` contract. Stickle then automatically:

1. **Tracks membership** - Maintains which models are currently in the segment
2. **Records history** - Tracks when models enter and leave segments
3. **Calculates statistics** - Aggregates tracked attributes at the segment level

For example, if you have a "High Value Customers" segment and track `mrr` as an attribute, Stickle automatically calculates the total MRR of all customers in that segment over time.

## Creating Segment Classes

Create a segment by extending the `Segment` contract. Segments should be placed in `app/Segments/` (or the namespace configured in `config/stickle.php`).

### Basic Segment Example

```php
<?php

namespace App\Segments;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\Segment;

#[StickleSegmentMetadata([
    'name' => 'Active Users',
    'description' => 'Users who have logged in within the last 7 days',
    'exportInterval' => 360, // Re-calculate every 6 hours
])]
class ActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->where('created_at', '>=', now()->subDays(7));
    }
}
```

**Required Elements:**
- `$model` - The model class this segment applies to (must use `StickleEntity` trait)
- `toBuilder()` - Method that returns an Eloquent Builder defining the segment criteria

### Segment Metadata

The `StickleSegmentMetadata` attribute provides additional context:

- `name` - Human-readable name (defaults to class name if omitted)
- `description` - Longer description of the segment's purpose
- `exportInterval` - How often to recalculate (in minutes). Overrides the global default from `config('stickle.schedule.exportSegments')`

## Using Filters in Segments

Stickle provides powerful filters for building segments based on user behavior and attributes:

### Filter by Recent Activity

```php
use StickleApp\Core\Filters\Filter;

class ActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->stickleWhere(
                Filter::eventCount('page_view')
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(7),
                        endDate: now()
                    )
                    ->greaterThan(0)
            );
    }
}
```

### Filter by Attribute Values

```php
class HighValueCustomers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->stickleWhere(
                Filter::number('lifetime_value')
                    ->greaterThan(10000)
            );
    }
}
```

### Combine Multiple Filters

```php
class AtRiskCustomers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            // High value customers
            ->stickleWhere(
                Filter::number('mrr')
                    ->greaterThan(500)
            )
            // But low recent activity
            ->stickleWhere(
                Filter::sessionCount()
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(30),
                        endDate: now()
                    )
                    ->lessThan(2)
            );
    }
}
```

### Mix Eloquent and Stickle Queries

```php
class PremiumActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            // Standard Eloquent query
            ->where('subscription_plan', 'premium')
            ->whereNotNull('email_verified_at')
            // Stickle filter
            ->stickleWhere(
                Filter::eventCount('feature_used')
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(14),
                        endDate: now()
                    )
                    ->greaterThan(10)
            );
    }
}
```

## Common Segment Examples

### Active Users

Users who have logged in recently:

```php
class ActiveUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->where('last_login_at', '>=', now()->subDays(7));
    }
}
```

### High-Value Customers

Customers above a revenue threshold:

```php
class HighValueCustomers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->stickleWhere(
                Filter::number('lifetime_value')
                    ->greaterThan(5000)
            );
    }
}
```

### At-Risk Customers

Customers showing signs of churn:

```php
class AtRiskCustomers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            // Haven't logged in for 14 days
            ->where('last_login_at', '<', now()->subDays(14))
            // But are paying customers
            ->where('subscription_status', 'active')
            // With declining usage
            ->stickleWhere(
                Filter::eventCount('page_view')
                    ->count()
                    ->decreased()
                    ->betweenDateRanges(
                        compareToDateRange: [now()->subDays(60), now()->subDays(30)],
                        currentDateRange: [now()->subDays(30), now()]
                    )
            );
    }
}
```

### Trial Users by Stage

Users in trial period, segmented by engagement:

```php
class EngagedTrialUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->where('subscription_status', 'trial')
            ->where('trial_ends_at', '>', now())
            ->stickleWhere(
                Filter::eventCount('key_feature_used')
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(7),
                        endDate: now()
                    )
                    ->greaterThan(3)
            );
    }
}
```

### Power Users

Highly engaged customers:

```php
class PowerUsers extends Segment
{
    public string $model = User::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            ->stickleWhere(
                Filter::sessionCount()
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(30),
                        endDate: now()
                    )
                    ->greaterThan(20)
            )
            ->stickleWhere(
                Filter::eventCount('feature_used')
                    ->count()
                    ->betweenDates(
                        startDate: now()->subDays(30),
                        endDate: now()
                    )
                    ->greaterThan(100)
            );
    }
}
```

### Ideal Customer Profile (ICP)

Customers matching your ideal profile:

```php
class IdealCustomerProfile extends Segment
{
    public string $model = Company::class;

    public function toBuilder(): Builder
    {
        return $this->model::query()
            // Company size
            ->where('employee_count', '>=', 50)
            ->where('employee_count', '<=', 500)
            // Industry
            ->whereIn('industry', ['technology', 'saas', 'software'])
            // Usage patterns
            ->stickleWhere(
                Filter::number('monthly_active_users')
                    ->greaterThan(10)
            )
            ->stickleWhere(
                Filter::number('mrr')
                    ->greaterThan(1000)
            );
    }
}
```

## How Stickle Tracks Segments

Once you create a segment, Stickle automatically tracks:

### Current Membership

Which models are currently in the segment:

```php
// Get all users in the Active Users segment
$activeUsers = User::stickleWhere(
    Filter::segment('ActiveUsers')->isInSegment()
)->get();
```

### Membership History

When models enter and leave segments:

```php
// Check if user has ever been in this segment
$everActive = User::stickleWhere(
    Filter::segment('ActiveUsers')->hasBeenInSegment()
)->exists();
```

### Aggregate Statistics

Stickle calculates aggregate values of tracked attributes for each segment over time. For example, if you track `mrr`:

- Total MRR in segment (sum)
- Average MRR per customer (avg)
- Highest/lowest MRR (max/min)
- Number of customers (count)

This data is available in StickleUI and via API endpoints.

### Recalculation Schedule

Segments are recalculated on a schedule (default: every 6 hours). Configure globally:

```env
STICKLE_FREQUENCY_EXPORT_SEGMENTS=360  # minutes
```

Or per-segment using metadata:

```php
#[StickleSegmentMetadata([
    'exportInterval' => 60,  # Recalculate hourly
])]
class CriticalSegment extends Segment
{
    // ...
}
```

## Querying by Segment

Stickle provides convenient Eloquent scopes for filtering by segment membership:

### Current Membership

```php
// Users currently in segment
User::stickleWhere(
    Filter::segment('ActiveUsers')->isInSegment()
)->get();

// Users NOT in segment
User::stickleWhere(
    Filter::segment('InactiveUsers')->isNotInSegment()
)->get();
```

### Historical Membership

```php
// Users who have ever been in segment
User::stickleWhere(
    Filter::segment('PowerUsers')->hasBeenInSegment()
)->get();

// Users who have never been in segment
User::stickleWhere(
    Filter::segment('PowerUsers')->neverBeenInSegment()
)->get();
```

### Combining Segment Filters

```php
// High value customers who are currently at risk
User::stickleWhere(
    Filter::segment('HighValueCustomers')->isInSegment()
)->stickleWhere(
    Filter::segment('AtRiskCustomers')->isInSegment()
)->get();
```

## Viewing Segments in StickleUI

After creating segments, view them in the dashboard:

- **Segment List** - `/stickle/{model}/segments` - See all segments for a model
- **Segment Details** - `/stickle/{model}/segments/{segmentId}` - View members and statistics
- **Segment Charts** - Historical trends of aggregate attributes
- **Export** - Download segment members as CSV

## Manual Segment Export

Force an immediate recalculation of all segments:

```bash
php artisan stickle:export-segments
```

For a specific segment:

```bash
php artisan stickle:export-segment App\\Segments\\ActiveUsers
```

## Performance Considerations

### Segment-Based Filters vs Direct Filters

Segment-based filtering uses pre-calculated data:

```php
// Uses pre-calculated segment (fast, but may be slightly stale)
User::stickleWhere(
    Filter::segment('ActiveUsers')->isInSegment()
)->get();
```

Direct filtering calculates in real-time:

```php
// Calculates in real-time (slower, but always current)
User::stickleWhere(
    Filter::sessionCount()
        ->count()
        ->betweenDates(now()->subDays(7), now())
        ->greaterThan(0)
)->get();
```

**Use segments when:**
- You query the same criteria frequently
- Real-time accuracy isn't critical
- You want to track trends over time

**Use direct filters when:**
- You need real-time results
- The criteria is used rarely
- The criteria changes frequently

## Best Practices

1. **Start with 5-10 key segments** - Don't create too many initially
2. **Name segments clearly** - Use descriptive names like "HighValueCustomers" not "HVC"
3. **Document complex logic** - Add comments explaining sophisticated filters
4. **Set appropriate intervals** - Critical segments can refresh hourly, others every 6-12 hours
5. **Test segment queries** - Use tinker to verify your segment logic returns expected results
6. **Monitor performance** - Complex segments with many filters may need optimization

## Next Steps

Now that you understand segments:

- **[Filters](/guide/filters)** - Master all available filter types
- **[Event Listeners](/guide/event-listeners)** - Respond when users enter/exit segments
- **[Recipes](/guide/recipes)** - See real-world segment examples
- **[API Endpoints](/guide/api-endpoints)** - Query segment data programmatically
