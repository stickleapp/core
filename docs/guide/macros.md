---
outline: deep
---

# Scopes and Macros

The `StickleEntity` trait provides powerful scopes and macros that extend Eloquent models with analytics capabilities. This document covers all available query scopes, model methods, and query builder macros.

## Query Scopes

### `stickle(Filter $filter)`

Apply Stickle filters to your model queries using the `stickle()` scope.

**Usage:**

```php
use StickleApp\Core\Filters\Base as Filter;

// Filter users with high ratings
$users = User::stickleWhere(
    Filter::number('user_rating')->greaterThan(4.0)
)->get();

// Filter by multiple conditions
$activeUsers = User::stickleWhere(
    Filter::eventCount('page_view')
        ->sum()
        ->greaterThan(10)
        ->betweenDates(now()->subDays(7), now())
)->get();

// Complex filtering with event aggregates
$highValueCustomers = Customer::stickleWhere(
    Filter::numberAggregate('mrr')
        ->sum()
        ->greaterThan(1000)
)->get();
```

### `stickleOrWhere(Filter $filter)`

Apply Stickle filters with OR logic to your queries.

**Usage:**

```php
// Find users who are either high-rated OR recently active
$users = User::stickleWhere(
    Filter::number('user_rating')->greaterThan(4.0)
)->stickleOrWhere(
    Filter::eventCount('login')
        ->greaterThan(0)
        ->betweenDates(now()->subDays(1), now())
)->get();
```

### Automatic Joins

Both scopes automatically handle the necessary joins to the `model_attributes` table and prevent duplicate joins when chaining multiple filters.

## Model Methods

### `stickleAttribute(string $attribute): mixed`

Retrieve the current value of a tracked attribute for a model instance.

**Usage:**

```php
$user = User::find(1);

// Get current user rating
$rating = $user->stickleAttribute('user_rating');

// Get subscription status
$status = $user->stickleAttribute('subscription_status');

// Returns null if attribute doesn't exist
$nonexistent = $user->stickleAttribute('invalid_attribute'); // null
```

### `trackableAttributes` (Accessor/Mutator)

Get or set multiple tracked attributes at once using the `trackable_attributes` property.

**Getting Attributes:**

```php
$user = User::find(1);

// Get all tracked attributes as array
$attributes = $user->trackable_attributes;
// Returns: ['user_rating' => 4.5, 'subscription_status' => 'active', ...]
```

**Setting Attributes:**

```php
$user = User::find(1);

// Set multiple attributes at once
$user->trackable_attributes = [
    'user_rating' => 4.8,
    'subscription_status' => 'premium',
    'last_login' => now()->toDateTimeString(),
];

// Attributes are automatically merged with existing ones and persisted
```

### `stickleRelationships(?array $relations = []): Collection`

Get all relationships with other StickleEntity models, including metadata.

**Usage:**

```php
$user = User::find(1);

// Get all relationships (HasMany and BelongsTo by default)
$relationships = $user->stickleRelationships();

// Get only specific relationship types
$hasMany = $user->stickleRelationships([HasMany::class]);

// Access relationship data
foreach ($relationships as $relationship) {
    echo $relationship->name;        // Relationship method name
    echo $relationship->type;        // Relationship type
    echo $relationship->related;     // Related model class
    // Additional metadata if defined via StickleRelationshipMetadata
}
```

## Static Methods

### `getStickleObservedAttributes(): array`

Get the list of attributes that should be automatically tracked when models are created or updated.

**Usage:**

```php
class User extends Model
{
    use StickleEntity;

    protected static $stickleObservedAttributes = [
        'email',
        'subscription_status',
        'user_rating',
    ];
}

// Get observed attributes
$observed = User::getStickleObservedAttributes();
// Returns: ['email', 'subscription_status', 'user_rating']
```

### `getStickleTrackedAttributes(): array`

Get the list of attributes that are tracked for analytics and charting.

**Usage:**

```php
class User extends Model
{
    use StickleEntity;

    protected static $stickleTrackedAttributes = [
        'user_rating',
        'subscription_mrr',
        'login_count',
    ];
}

// Get tracked attributes
$tracked = User::getStickleTrackedAttributes();
// Returns: ['user_rating', 'subscription_mrr', 'login_count']
```

### `getStickleChartData(): array`

Get chart configuration data for all tracked attributes, including metadata.

**Usage:**

```php
$chartData = User::getStickleChartData();

// Returns array of chart configurations:
[
    [
        'key' => 'user_rating',
        'modelClass' => 'App\\Models\\User',
        'attribute' => 'user_rating',
        'chartType' => ChartType::LINE,
        'label' => 'User Rating',
        'description' => 'Average user satisfaction rating',
        'dataType' => DataType::NUMBER,
        'primaryAggregateType' => PrimaryAggregate::AVG,
    ],
    // ... more attributes
]
```

## Eloquent Relationships

### `modelAttributes(): HasOne`

One-to-one relationship with the current tracked attributes.

**Usage:**

```php
$user = User::with('modelAttributes')->find(1);

// Access current attribute data
$currentData = $user->modelAttributes->data;

// Check when attributes were last synced
$syncedAt = $user->modelAttributes->synced_at;
```

### `modelAttributeAudits(): HasMany`

One-to-many relationship with attribute change history.

**Usage:**

```php
$user = User::with('modelAttributeAudits')->find(1);

// Get all attribute changes
foreach ($user->modelAttributeAudits as $audit) {
    echo $audit->attribute_name;  // Which attribute changed
    echo $audit->old_value;       // Previous value
    echo $audit->new_value;       // New value
    echo $audit->created_at;      // When it changed
}

// Get changes for specific attribute
$ratingChanges = $user->modelAttributeAudits()
    ->where('attribute_name', 'user_rating')
    ->orderBy('created_at', 'desc')
    ->get();
```

### `modelRelationshipStatistics(): HasMany`

One-to-many relationship with relationship aggregate statistics.

**Usage:**

```php
$customer = Customer::with('modelRelationshipStatistics')->find(1);

// Get relationship statistics
foreach ($customer->modelRelationshipStatistics as $stat) {
    echo $stat->relationship_name;    // e.g., 'users'
    echo $stat->attribute_name;       // e.g., 'user_rating'
    echo $stat->count;               // Number of related records
    echo $stat->sum;                 // Sum of attribute values
    echo $stat->avg;                 // Average value
    echo $stat->min;                 // Minimum value
    echo $stat->max;                 // Maximum value
}
```

## Query Builder Macros

The `StickleEntity` trait adds macros to Laravel's Query Builder for enhanced functionality.

### `hasJoin(string $table, ?string $alias = null): bool`

Check if a join already exists on the query to prevent duplicate joins.

**Usage:**

```php
$query = User::query();

if (!$query->hasJoin('model_attributes')) {
    $query->join('model_attributes', 'users.id', '=', 'model_attributes.object_uid');
}
```

### `joinRelationship(Relation $relation, string $alias, string $joinType = 'inner'): Builder`

Join a relationship with support for aliasing and different join types.

**Usage:**

```php
$user = new User();
$ordersRelation = $user->orders();

$results = User::query()
    ->joinRelationship($ordersRelation, 'user_orders', 'left')
    ->select('users.*', 'user_orders.total')
    ->get();
```

## Practical Examples

### Basic Attribute Tracking

```php
class User extends Model
{
    use StickleEntity;

    // Define which attributes to observe for changes
    protected static $stickleObservedAttributes = [
        'email',
        'subscription_status',
        'user_rating',
    ];

    // Define which attributes to track for analytics
    protected static $stickleTrackedAttributes = [
        'user_rating',
        'login_count',
    ];
}

// Tracked attributes are automatically updated when model changes
$user = User::find(1);
$user->user_rating = 4.5;
$user->save(); // Automatically tracked

// Access current tracked value
$rating = $user->stickleAttribute('user_rating'); // 4.5

// Manual attribute setting
$user->trackable_attributes = [
    'login_count' => 150,
    'last_activity' => now()->toDateTimeString(),
];
```

### Advanced Filtering

```php
use StickleApp\Core\Filters\Base as Filter;

// Find high-value customers with recent activity
$customers = Customer::stickleWhere(
    Filter::numberAggregate('mrr')
        ->sum()
        ->greaterThan(500)
)->stickleWhere(
    Filter::eventCount('login')
        ->count()
        ->greaterThan(0)
        ->betweenDates(now()->subDays(30), now())
)->get();

// Find users who either have high ratings OR are recently active
$engagedUsers = User::stickleWhere(
    Filter::number('user_rating')->greaterThan(4.0)
)->stickleOrWhere(
    Filter::eventCount('page_view')
        ->count()
        ->greaterThan(10)
        ->betweenDates(now()->subDays(7), now())
)->get();
```

### Relationship Analytics

```php
class Customer extends Model
{
    use StickleEntity;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    #[StickleRelationshipMetadata([
        'label' => 'Customer Users',
        'description' => 'Users associated with this customer',
        'tracked_attributes' => ['user_rating', 'subscription_status']
    ])]
    public function activeUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('status', 'active');
    }
}

$customer = Customer::find(1);

// Get all StickleEntity relationships
$relationships = $customer->stickleRelationships();

// Access relationship statistics
$stats = $customer->modelRelationshipStatistics()
    ->where('relationship_name', 'users')
    ->where('attribute_name', 'user_rating')
    ->first();

echo "Average user rating: {$stats->avg}";
echo "Total users: {$stats->count}";
```

### Chart Data Configuration

```php
class User extends Model
{
    use StickleEntity;

    protected static $stickleTrackedAttributes = [
        'user_rating',
        'subscription_mrr',
    ];

    #[StickleAttributeMetadata([
        'chartType' => ChartType::LINE,
        'label' => 'User Satisfaction',
        'description' => 'Average user satisfaction rating',
        'dataType' => DataType::NUMBER,
        'primaryAggregateType' => PrimaryAggregate::AVG,
    ])]
    public function userRating(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (float) $value,
            set: fn ($value) => (float) $value,
        );
    }
}

// Get chart configuration for UI components
$chartData = User::getStickleChartData();

// Use in Blade templates or API responses
foreach ($chartData as $chart) {
    echo "Chart: {$chart['label']} ({$chart['chartType']->value})";
}
```

## Performance Considerations

### Automatic Join Prevention

The `stickle()` and `stickleOrWhere()` scopes automatically prevent duplicate joins:

```php
// This won't create duplicate joins
$users = User::stickleWhere(
    Filter::number('user_rating')->greaterThan(4.0)
)->stickleWhere(
    Filter::number('subscription_mrr')->greaterThan(100)
)->get();
```

### Eager Loading

When working with relationships, use eager loading to prevent N+1 queries:

```php
// Load tracked attributes and audit history efficiently
$users = User::with([
    'modelAttributes',
    'modelAttributeAudits' => function ($query) {
        $query->where('created_at', '>=', now()->subDays(30));
    }
])->get();
```

### Batch Attribute Updates

For bulk operations, consider using the accessor/mutator pattern:

```php
$users = User::whereIn('id', $userIds)->get();

foreach ($users as $user) {
    $user->trackable_attributes = [
        'last_batch_update' => now()->toDateTimeString(),
        'batch_id' => $batchId,
    ];
}
```

## Best Practices

1. **Define Tracked Attributes Clearly**: Use separate arrays for `$stickleObservedAttributes` and `$stickleTrackedAttributes` based on your needs.

2. **Use Metadata Attributes**: Define `StickleAttributeMetadata` and `StickleRelationshipMetadata` for better UI integration.

3. **Leverage Filtering**: Use the powerful filtering system instead of raw database queries when possible.

4. **Monitor Performance**: Use eager loading and be mindful of automatic joins when building complex queries.

5. **Attribute Naming**: Use consistent, descriptive names for tracked attributes to improve analytics clarity.
