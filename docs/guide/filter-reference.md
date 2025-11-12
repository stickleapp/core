---
outline: deep
---

# Filter Reference

Quick reference for all Stickle filter types and their methods. For detailed examples and usage, see the [Filters Guide](/guide/filters).

## Filter Types Overview

| Filter Type | Use For | Requires Date Range |
|------------|---------|---------------------|
| Boolean | True/false attributes | No |
| Date | Date attributes | No |
| Datetime | Datetime attributes | No |
| EventCount | Counting events over time | Yes |
| Number | Numeric attributes and aggregations | Optional |
| RequestCount | Page view counts over time | Yes |
| SessionCount | Session counts over time | Yes |
| Segment | Pre-computed segment membership | No |
| Text | String attributes | No |

## Boolean Filters

```php
Filter::boolean('attribute_name')
```

**Available Methods:**
- `->isTrue()` - Attribute is true
- `->isFalse()` - Attribute is false
- `->isNotTrue()` - Attribute is not true (false or null)
- `->isNotFalse()` - Attribute is not false (true or null)
- `->isNull()` - Attribute is null
- `->isNotNull()` - Attribute is not null

## Date Filters

```php
Filter::date('attribute_name')
```

**Available Methods:**
- `->equals(date)` - Exact match
- `->isBefore(date)` - Before given date
- `->isAfter(date)` - After given date
- `->occurredBefore(date)` - Alias for isBefore
- `->occurredAfter(date)` - Alias for isAfter
- `->willOccurBefore(date)` - Future date before
- `->willOccurAfter(date)` - Future date after
- `->between(startDate, endDate)` - Between two dates

## Datetime Filters

```php
Filter::datetime('attribute_name')
```

**Available Methods:**
- `->equals(datetime)` - Exact match
- `->isBefore(datetime)` - Before given datetime
- `->isAfter(datetime)` - After given datetime
- `->occurredBefore(datetime)` - Alias for isBefore
- `->occurredAfter(datetime)` - Alias for isAfter
- `->willOccurBefore(datetime)` - Future datetime before
- `->willOccurAfter(datetime)` - Future datetime after
- `->between(startDatetime, endDatetime)` - Between two datetimes

## EventCount Filters

```php
Filter::eventCount('event_name')
```

**Required Methods (chain in order):**
1. Aggregate: `->count()`, `->sum()`, `->avg()`, `->min()`, `->max()`
2. Date range: `->betweenDates()` or `->betweenDateRanges()`
3. Comparison: See comparison methods below

**Date Range Methods:**
- `->betweenDates(startDate: $start, endDate: $end)`
- `->betweenDateRanges(compareToDateRange: [$start, $end], currentDateRange: [$start, $end])`

**Delta Methods (for betweenDateRanges):**
- `->increased()` - Value increased
- `->decreased()` - Value decreased
- `->changed()` - Value changed (either direction)

**Comparison Methods:**
- `->equals(value)`
- `->greaterThan(value)`
- `->lessThan(value)`
- `->greaterThanOrEqualTo(value)`
- `->lessThanOrEqualTo(value)`
- `->between(min, max)`

## Number Filters

```php
Filter::number('attribute_name')
```

**Simple Comparison (no aggregation needed):**
- `->equals(value)`
- `->greaterThan(value)`
- `->lessThan(value)`
- `->greaterThanOrEqualTo(value)`
- `->lessThanOrEqualTo(value)`
- `->between(min, max)`

**With Aggregation Over Time:**
1. Aggregate: `->count()`, `->sum()`, `->avg()`, `->min()`, `->max()`
2. Date range: `->betweenDates()` or `->betweenDateRanges()`
3. Optional delta: `->increased()`, `->decreased()`, `->changed()`
4. Comparison: See comparison methods above

## RequestCount Filters

```php
Filter::requestCount('url_or_path')
```

**Required Methods (chain in order):**
1. Aggregate: `->count()`, `->sum()`, `->avg()`, `->min()`, `->max()`
2. Date range: `->betweenDates()` or `->betweenDateRanges()`
3. Optional delta: `->increased()`, `->decreased()`, `->changed()`
4. Comparison: See EventCount comparison methods

## SessionCount Filters

```php
Filter::sessionCount()
```

**Required Methods (chain in order):**
1. Aggregate: `->count()`, `->sum()`, `->avg()`, `->min()`, `->max()`
2. Date range: `->betweenDates()` or `->betweenDateRanges()`
3. Optional delta: `->increased()`, `->decreased()`, `->changed()`
4. Comparison: See EventCount comparison methods

## Segment Filters

```php
Filter::segment('SegmentClassName')
```

**Available Methods:**
- `->isInSegment()` - Currently in segment
- `->isNotInSegment()` - Not currently in segment
- `->hasBeenInSegment()` - Has ever been in segment
- `->neverBeenInSegment()` - Never been in segment

## Text Filters

```php
Filter::text('attribute_name')
```

**Available Methods:**
- `->equals(text)` - Exact match
- `->contains(text)` - Contains substring
- `->beginsWith(text)` - Starts with substring

## Usage Examples

### Simple Filters

```php
// Boolean
User::stickleWhere(Filter::boolean('is_premium')->isTrue())->get();

// Text
User::stickleWhere(Filter::text('company')->equals('Acme Corp'))->get();

// Date
User::stickleWhere(Filter::date('trial_ends')->isAfter(now()))->get();
```

### Time-Based Filters

```php
// Event count last 30 days
User::stickleWhere(
    Filter::eventCount('page_view')
        ->count()
        ->betweenDates(now()->subDays(30), now())
        ->greaterThan(10)
)->get();

// Sessions increased month over month
User::stickleWhere(
    Filter::sessionCount()
        ->count()
        ->increased()
        ->betweenDateRanges(
            compareToDateRange: [now()->subMonths(2), now()->subMonth()],
            currentDateRange: [now()->subMonth(), now()]
        )
        ->greaterThan(0)
)->get();
```

### Combining Filters

```php
// AND condition
User::stickleWhere(Filter::boolean('is_premium')->isTrue())
    ->stickleWhere(Filter::number('mrr')->greaterThan(100))
    ->get();

// OR condition
User::stickleWhere(Filter::segment('HighValue')->isInSegment())
    ->stickleOrWhere(Filter::segment('PowerUser')->isInSegment())
    ->get();
```

## Common Patterns

### Active Users (last 7 days)
```php
Filter::sessionCount()
    ->count()
    ->betweenDates(now()->subDays(7), now())
    ->greaterThan(0)
```

### High-Value Customers (LTV > $5000)
```php
Filter::number('lifetime_value')->greaterThan(5000)
```

### Growing Engagement (page views increased)
```php
Filter::eventCount('page_view')
    ->count()
    ->increased()
    ->betweenDateRanges(
        compareToDateRange: [now()->subDays(60), now()->subDays(30)],
        currentDateRange: [now()->subDays(30), now()]
    )
    ->greaterThan(5)
```

### At-Risk Customers (inactive + paying)
```php
User::where('subscription_status', 'active')
    ->stickleWhere(
        Filter::sessionCount()
            ->count()
            ->betweenDates(now()->subDays(30), now())
            ->lessThan(2)
    )
```

## Next Steps

- **[Filters Guide](/guide/filters)** - Detailed examples and usage patterns
- **[Customer Segments](/guide/segments)** - Build segments using filters
- **[Tracking Attributes](/guide/tracking-attributes)** - Define attributes to filter on
