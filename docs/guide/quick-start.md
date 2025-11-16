---
outline: deep
---

# Quick Start Guide

Get Stickle up and running in your Laravel application in 15 minutes. This guide will walk you through the essentials: installation, adding the `StickleEntity` trait, tracking your first attribute, creating your first segment, and viewing your data.

## Prerequisites

Before you begin, make sure you have completed the **[Installation Guide](/guide/installation)**. You should have:

- Stickle installed via Composer
- Set your initial configuration
- Migrations run
- Required services running (queue worker, scheduled tasks, and optionally Reverb)

With your installation complete and required services running, you are ready to begin customizing your Stickle conguration.

## Step 1: Add StickleEntity Trait

Add the `StickleEntity` trait to your "customer" model or models. These are models that you want to track. Typically this is "Users" and often a table such as "Customers", "Accounts" or "Tenants". 

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use StickleApp\Core\Traits\StickleEntity;

class User extends Authenticatable
{
    use StickleEntity;

    // Define attributes that Stickle should track
    public static function stickleTrackedAttributes(): array
    {
        return [
            'name',
            'email',
            'created_at',
        ];
    }

    // Define attributes that trigger events when changed
    public static function stickleObservedAttributes(): array
    {
        return [
            'email',
            'email_verified_at',
        ];
    }
}
```

**What this does:**
- `stickleTrackedAttributes()` - Attributes that Stickle tracks over time for analytics
- `stickleObservedAttributes()` - Attributes that dispatch events when they change

## Step 2: Track a Custom Attribute

Let's add a calculated attribute that tracks how many days since a user registered:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use StickleApp\Core\Traits\StickleEntity;

class User extends Authenticatable
{
    use StickleEntity;

    public static function stickleTrackedAttributes(): array
    {
        return [
            'name',
            'email',
            'created_at',
            'days_since_signup', // Add our custom attribute
        ];
    }

    public static function stickleObservedAttributes(): array
    {
        return [
            'email',
            'email_verified_at',
        ];
    }

    // Custom attribute accessor
    protected function daysSinceSignup(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->created_at?->diffInDays(now()) ?? 0,
        );
    }
}
```

Don't forget the import at the top:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;
```

::: tip Manually Sync Attributes
After adding new tracked attributes to your models, you can manually trigger a sync:

```bash
# TODO: Create stickle:sync-attributes command
php artisan stickle:sync-attributes
```

This will immediately record the new attributes for all your models. Otherwise, attributes are synced automatically on the schedule.
:::

## Step 3: Create Your First Segment

Create a segment to identify active users. First, create the segments directory:

```bash
mkdir -p app/Segments
```

Create `app/Segments/ActiveUsers.php`:

```php
<?php

namespace App\Segments;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use StickleApp\Core\Attributes\StickleSegmentMetadata;
use StickleApp\Core\Contracts\Segment;
use StickleApp\Core\Filters\Filter;

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

**What this does:**
- Defines a segment of users who have viewed at least one page in the last 7 days
- Automatically recalculates every 6 hours
- Tracks segment membership over time

::: tip Manually Sync Segments
After creating new segments, you can manually trigger an export:

```bash
# TODO: Create stickle:sync-segments command
php artisan stickle:sync-segments
```

This will immediately export your new segments. Otherwise, segments are exported automatically on the schedule (every 5 minutes).
:::

## Step 4: View Your Data in StickleUI

Stickle is now tracking your users! Open your application in a browser and navigate to:

```
http://your-app.test/stickle
```

You'll see:

- **User List** - All users with tracked attributes
- **User Details** - Click any user to see their attribute history
- **Segments** - View your "Active Users" segment
- **Events** - Real-time stream of page views and events

### What to explore:

1. **Browse Users** - `/stickle/user` - See all your users
2. **View a User** - Click on any user to see their history
3. **Check Segments** - `/stickle/user/segments` - See your Active Users segment
4. **Watch Events** - `/stickle/events` - Real-time event stream

## Step 5: Test It Out

Let's generate some activity to see Stickle in action:

1. **Create a test user** (if you don't have any):

```bash
php artisan tinker
```

```php
User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);
```

2. **Log in as that user** and browse a few pages

3. **Go back to StickleUI** (`/stickle`) and you'll see:
   - The user appears in your user list
   - Their page views in the events stream
   - They appear in the Active Users segment (if they viewed pages)

## What's Next?

You've now got Stickle running! Here's what to explore next:

### Learn Core Features

- **[Tracking Attributes](/guide/tracking-attributes)** - Master attribute tracking and aggregation
- **[Customer Segments](/guide/segments)** - Build more sophisticated segments
- **[Filters](/guide/filters)** - Learn all the powerful filtering options
- **[Event Listeners](/guide/event-listeners)** - Respond to user actions in real-time

### Build Something Useful

Check out our **[Recipes](/guide/recipes)** for common patterns:
- Track Monthly Recurring Revenue (MRR)
- Identify churning customers
- Find power users
- Send emails when users enter segments

### Go to Production

When you're ready to deploy:
- Read the **[Deployment Guide](/guide/deployment)** for production best practices
- Configure queue workers and scheduled tasks
- Optimize database performance

## Common Issues

### "Class StickleEntity not found"

Make sure you ran `composer require stickleapp/core` and the package is installed.

### "Segment not showing up"

Segments are calculated on a schedule. Wait a few minutes or manually trigger:

```bash
php artisan stickle:export-segments
```

### "No events appearing"

Make sure:
1. You're logged in as an authenticated user
2. The JavaScript tracking code is injected (check `config/stickle.php`)
3. Queue workers are running (`php artisan schedule:work`)

For more help, see our **[Troubleshooting Guide](/guide/troubleshooting)**.

## You're All Set! 🎉

You've successfully installed Stickle, set up tracking, created a segment, and viewed your data. You're now ready to build powerful customer analytics into your Laravel application.

Happy tracking!
