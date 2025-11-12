---
outline: deep
---

# Tracking Attributes

Stickle allows you to track and audit changes to your model attributes over time. By tracking attributes, you can analyze trends, filter customers by historical data, and understand how key metrics evolve.

## What are Attributes?

Attributes in Stickle are properties of your models that you want to monitor over time. These can be:

- **Standard model columns** - `email`, `name`, `created_at`
- **Calculated attributes** - `days_since_signup`, `lifetime_value`
- **Business metrics** - `mrr`, `plan_level`, `account_health_score`
- **Relationship aggregates** - `total_orders`, `average_order_value`

Stickle tracks these attributes and maintains a complete audit history, enabling powerful analytics and filtering capabilities.

## Observed vs Tracked Attributes

Stickle distinguishes between two types of attributes:

### Observed Attributes

**Observed attributes** are monitored for changes. When an observed attribute changes, Stickle dispatches an `ObjectAttributeChanged` event, allowing you to respond in real-time.

Use observed attributes for:
- Triggering notifications when values change
- Responding to status transitions
- Audit logging critical fields

### Tracked Attributes

**Tracked attributes** are recorded periodically for analytics purposes. Stickle stores historical values, enabling trend analysis and time-based filtering.

Use tracked attributes for:
- Displaying charts and graphs
- Calculating deltas and growth rates
- Filtering by historical values
- Segment-level aggregation

## Defining Trackable Attributes

Add the `StickleEntity` trait to your model and define which attributes to track:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Traits\StickleEntity;

class User extends Model
{
    use StickleEntity;

    /**
     * Attributes that dispatch events when changed
     */
    public static function stickleObservedAttributes(): array
    {
        return [
            'email',
            'email_verified_at',
            'subscription_status',
        ];
    }

    /**
     * Attributes tracked for analytics over time
     */
    public static function stickleTrackedAttributes(): array
    {
        return [
            'name',
            'email',
            'subscription_mrr',
            'total_orders',
            'account_health_score',
        ];
    }
}
```

**Key Points:**
- `stickleObservedAttributes()` - Changes trigger events immediately
- `stickleTrackedAttributes()` - Values are recorded periodically and during model updates
- An attribute can be both observed AND tracked

## Accessing Attributes

### Get Current Attribute Value

Use the `stickleAttribute()` method to retrieve the current tracked value:

```php
$user = User::find(1);

// Get current MRR
$mrr = $user->stickleAttribute('subscription_mrr');

// Get subscription status
$status = $user->stickleAttribute('subscription_status');

// Returns null if attribute doesn't exist
$invalid = $user->stickleAttribute('nonexistent'); // null
```

### Get Multiple Attributes

Use the `trackable_attributes` accessor to get all tracked attributes at once:

```php
$user = User::find(1);

// Get all tracked attributes as array
$attributes = $user->trackable_attributes;
// Returns: ['subscription_mrr' => 99.00, 'total_orders' => 15, ...]
```

### Set Multiple Attributes

Use the `trackable_attributes` mutator to set multiple attributes at once:

```php
$user = User::find(1);

// Set multiple attributes
$user->trackable_attributes = [
    'subscription_mrr' => 149.00,
    'account_health_score' => 85,
    'last_login' => now()->toDateTimeString(),
];

// Attributes are automatically persisted
```

## Attribute Types

### Numeric Attributes

Track numbers like revenue, counts, scores, or ratings:

```php
class User extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'subscription_mrr',      // Monthly Recurring Revenue
            'lifetime_value',        // Total customer value
            'account_health_score',  // 0-100 health score
            'total_logins',         // Login count
        ];
    }

    // Calculated attribute example
    protected function lifetimeValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orders()->sum('total')
        );
    }
}
```

**Use numeric attributes for:**
- Revenue tracking (MRR, LTV)
- Engagement metrics (login count, page views)
- Health scores
- Aggregate calculations

### Text Attributes

Track string values like status, plan names, or categories:

```php
class User extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'subscription_plan',    // "starter", "pro", "enterprise"
            'account_status',       // "active", "churned", "at-risk"
            'primary_feature',      // Feature they use most
        ];
    }
}
```

**Use text attributes for:**
- Subscription plans/tiers
- User status or lifecycle stage
- Categories or tags
- Feature usage patterns

### Boolean Attributes

Track true/false values:

```php
class User extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'is_premium',
            'has_onboarded',
            'newsletter_subscribed',
        ];
    }
}
```

### Date Attributes

Track important dates:

```php
class User extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'trial_expires_at',
            'subscription_renewal_date',
            'last_login_at',
        ];
    }
}
```

## Parent-Child Aggregation

Stickle automatically aggregates child model attributes up to parent models. This is powerful for multi-level hierarchies like Company → Team → User.

### Example: Aggregating User Ratings to Company

```php
class Company extends Model
{
    use StickleEntity;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function stickleTrackedAttributes(): array
    {
        return [
            'company_name',
            'mrr',
        ];
    }
}

class User extends Model
{
    use StickleEntity;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function stickleTrackedAttributes(): array
    {
        return [
            'user_rating',  // 1-5 star rating
        ];
    }
}
```

**Access aggregate statistics:**

```php
$company = Company::find(1);

// Get aggregate user ratings for this company
$stats = $company->modelRelationshipStatistics()
    ->where('relationship_name', 'users')
    ->where('attribute_name', 'user_rating')
    ->first();

echo "Average user rating: {$stats->avg}";
echo "Total rated users: {$stats->count}";
echo "Highest rating: {$stats->max}";
echo "Lowest rating: {$stats->min}";
echo "Sum of all ratings: {$stats->sum}";
```

**Stickle provides:**
- `count` - Number of related records with the attribute
- `avg` - Average value
- `sum` - Sum of all values
- `min` - Minimum value
- `max` - Maximum value

### Multi-Level Aggregation

For hierarchies like ParentCompany → Company → User, aggregates roll up through all levels:

```php
// Get aggregated metrics from grandchild models
$parentCompany = ParentCompany::find(1);

$userStats = $parentCompany->modelRelationshipStatistics()
    ->where('relationship_name', 'companies')
    ->where('attribute_name', 'user_rating')
    ->first();

// This includes ratings from users across ALL child companies
```

## Complete Examples

### Track Customer Health Score

```php
class Customer extends Model
{
    use StickleEntity;

    public static function stickleObservedAttributes(): array
    {
        return [
            'health_score',  // Alert when health drops
        ];
    }

    public static function stickleTrackedAttributes(): array
    {
        return [
            'health_score',
            'last_login_days_ago',
            'feature_adoption_percentage',
        ];
    }

    protected function healthScore(): Attribute
    {
        return Attribute::make(
            get: function () {
                $score = 100;

                // Deduct for inactivity
                $daysSinceLogin = $this->last_login_at?->diffInDays(now()) ?? 999;
                if ($daysSinceLogin > 30) $score -= 40;
                elseif ($daysSinceLogin > 14) $score -= 20;
                elseif ($daysSinceLogin > 7) $score -= 10;

                // Bonus for high usage
                if ($this->monthly_active_days > 20) $score += 10;

                return max(0, min(100, $score));
            }
        );
    }
}
```

### Track MRR and Expansion Revenue

```php
class Account extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'mrr',
            'previous_mrr',
            'expansion_revenue',
        ];
    }

    protected function mrr(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->subscriptions()
                ->where('status', 'active')
                ->sum('monthly_amount')
        );
    }

    protected function expansionRevenue(): Attribute
    {
        return Attribute::make(
            get: function () {
                $current = $this->mrr;
                $previous = $this->stickleAttribute('previous_mrr') ?? 0;
                return max(0, $current - $previous);
            }
        );
    }
}
```

### Track Feature Adoption

```php
class User extends Model
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'features_used_count',
            'core_features_adopted',
            'advanced_features_adopted',
        ];
    }

    protected function featuresUsedCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->featureUsage()
                ->where('last_used_at', '>=', now()->subDays(30))
                ->count()
        );
    }
}
```

## Attribute Metadata

Add metadata to attributes for better UI integration:

```php
use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;
use StickleApp\Core\Enums\PrimaryAggregate;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    use StickleEntity;

    #[StickleAttributeMetadata([
        'chartType' => ChartType::LINE,
        'label' => 'Monthly Recurring Revenue',
        'description' => 'Current MRR from active subscriptions',
        'dataType' => DataType::NUMBER,
        'primaryAggregateType' => PrimaryAggregate::SUM,
    ])]
    protected function subscriptionMrr(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->activeSubscription?->monthly_amount ?? 0
        );
    }
}
```

## Performance Considerations

### Automatic Updates

Attributes in `stickleObservedAttributes()` are updated automatically when the model is saved:

```php
$user = User::find(1);
$user->email = 'newemail@example.com';
$user->save();
// ObjectAttributeChanged event dispatched immediately
```

### Scheduled Updates

Calculated attributes in `stickleTrackedAttributes()` are updated on a schedule (default: every 6 hours). Configure the frequency:

```env
STICKLE_FREQUENCY_RECORD_MODEL_ATTRIBUTES=360  # minutes
```

### Manual Updates

Force an immediate attribute sync:

```php
$user = User::find(1);
$user->trackable_attributes = [
    'calculated_field' => $someValue,
];
// Saves immediately
```

## Best Practices

1. **Start Simple** - Track 5-10 key attributes initially
2. **Use Observed Attributes Sparingly** - Only for attributes that need immediate response
3. **Calculate at Runtime** - Use Eloquent accessors for calculated attributes
4. **Name Clearly** - Use descriptive names like `subscription_mrr` not `mrr`
5. **Document Calculations** - Add comments explaining complex calculated attributes
6. **Test Accessors** - Ensure calculated attributes return expected values

## Next Steps

Now that you understand attribute tracking:

- **[Customer Segments](/guide/segments)** - Use attributes to build powerful segments
- **[Filters](/guide/filters)** - Filter customers by attribute values
- **[Event Listeners](/guide/event-listeners)** - Respond to attribute changes
- **[Recipes](/guide/recipes)** - See real-world attribute tracking examples
