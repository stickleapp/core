---
outline: deep
---

# StickleUI Dashboard

StickleUI is the web interface bundled with Stickle Core that provides a comprehensive dashboard for viewing customer analytics, segments, and model data.

## What is StickleUI?

StickleUI is a pre-built, zero-dependency analytics dashboard that runs directly in your Laravel application. Built with Laravel Blade, Tailwind CSS, Alpine.js, and Chart.js, it provides:

- **Customer browsing** - View all your customers and their details
- **Real-time events** - Live stream of user actions
- **Segment analytics** - See who's in each segment
- **Historical data** - Charts and trends over time
- **Relationship data** - Navigate between related models

## Accessing the Dashboard

By default, StickleUI is accessible at `/stickle` from your Laravel application:

```
https://yourapp.com/stickle/
```

### Authentication

StickleUI uses the `web` middleware by default. Configure additional middleware in `config/stickle.php`:

```php
'routes' => [
    'web' => [
        'prefix' => 'stickle',
        'middleware' => ['web', 'auth', 'can:view-analytics'],
    ],
],
```

## Main Interface Components

### Dashboard (Live View)

**URL:** `/stickle/` or `/stickle/live`

The main dashboard showing:
- Live analytics and real-time data
- Recent activity across all customers
- Key metrics and statistics
- Real-time updates via WebSockets

### Model Management

**URL Pattern:** `/stickle/{modelClass}`

**Example:** `/stickle/user`

View all instances of a specific model:
- Searchable, sortable data tables
- Tracked attributes displayed
- Quick filters
- Export capabilities

### Individual Model View

**URL Pattern:** `/stickle/{modelClass}/{uid}`

**Example:** `/stickle/user/123`

Detailed view of a specific model instance:
- Model attributes timeline showing changes over time
- Event history for this user
- Related models via relationships
- Interactive charts of tracked attributes
- Session history

### Model Relationships

**URL Pattern:** `/stickle/{modelClass}/{uid}/{relationship}`

**Example:** `/stickle/company/456/users`

Explore relationships between models:
- List of related models
- Relationship statistics and aggregates
- Drill down into related data

### Segments Management

**URL Pattern:** `/stickle/{modelClass}/segments`

**Example:** `/stickle/user/segments`

View and manage customer segments:
- List all segments for a model type
- Real-time segment population counts
- Segment performance metrics over time
- Click through to see segment members

### Individual Segment View

**URL Pattern:** `/stickle/{modelClass}/segments/{segmentId}`

**Example:** `/stickle/user/segments/1`

Detailed analysis of a specific segment:
- List of all members in the segment
- Historical performance data and charts
- Aggregate statistics for tracked attributes
- Export members to CSV

## Key Features

### Navigation

- **Sidebar Navigation** - Access different model types and segments
- **Breadcrumb Navigation** - Clear indication of current location
- **Responsive Design** - Mobile-friendly with collapsible sidebar
- **Search** - Quick search across models

### Real-time Updates

StickleUI includes built-in real-time capabilities:
- **Laravel Echo** integration for WebSocket connections
- **Reverb/Pusher** broadcasting support
- **Automatic UI updates** when data changes
- **Live event stream** on dashboard

### Interactive Components

- **Data Tables** - Sortable, searchable, paginated tables
- **Charts** - Interactive visualizations using Chart.js
- **Tabs** - Organized content navigation
- **Filters** - Dynamic filtering of data

### Data Visualization

- **Line Charts** - Trend analysis over time
- **Delta Indicators** - Change metrics with visual indicators
- **Statistics Cards** - Key metric summaries
- **Timeline Views** - Event chronology display

## Customization

### Theming

StickleUI uses Tailwind CSS and can be customized by modifying your Tailwind configuration:

```javascript
// tailwind.config.js
export default {
    content: [
        './vendor/stickleapp/core/resources/views/**/*.blade.php',
        // ... your other paths
    ],
    theme: {
        extend: {
            colors: {
                // Override Stickle colors
                primary: {
                    50: '#eff6ff',
                    // ... your brand colors
                },
            },
        },
    },
}
```

### Adding Custom Pages

Create custom pages within StickleUI by adding routes and views:

**1. Add a route:**

```php
// routes/web.php
Route::middleware(['web', 'auth'])
    ->prefix('stickle')
    ->group(function () {
        Route::get('/custom-report', [CustomReportController::class, 'index']);
    });
```

**2. Create a view using Stickle's layout:**

```blade
{{-- resources/views/stickle/custom-report.blade.php --}}
@extends('stickle::layouts.default')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Custom Report</h1>

        {{-- Your custom content --}}

        {{-- Use Stickle components --}}
        <x-stickle::chart
            :data="$chartData"
            type="line"
            title="Custom Metric"
        />
    </div>
@endsection
```

### Overriding Components

Override Stickle's Blade components by publishing and modifying them:

```bash
php artisan vendor:publish --tag=stickle-views
```

This publishes views to `resources/views/vendor/stickle/`, which take precedence over package views.

### Adding Custom Charts

Integrate your own Chart.js visualizations:

```blade
<div class="bg-white rounded-lg shadow p-6">
    <canvas id="myChart"></canvas>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('myChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'My Dataset',
            data: @json($data),
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
    }
});
</script>
@endpush
```

### Customizing the Menu

Extend the sidebar menu by modifying the navigation partial:

```blade
{{-- resources/views/vendor/stickle/partials/navigation.blade.php --}}

{{-- Add your custom menu items --}}
<a href="/stickle/custom-report"
   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
    <svg class="w-5 h-5 mr-3"><!-- icon --></svg>
    Custom Report
</a>
```

## Demo Environment

StickleUI includes a demo interface at `/stickle/demo`:

- **Live application simulation** - See Stickle in action
- **Administrative interface preview** - Explore features
- **Integration examples** - Learn implementation patterns

## Technical Architecture

### Frontend Stack

- **Blade Templates** - Server-side rendering
- **Tailwind CSS** - Utility-first styling
- **Alpine.js** - Reactive JavaScript framework
- **Chart.js** - Data visualization library
- **Simple-DataTables** - Table functionality

### Layout Structure

Stickle uses a default layout that you can extend:

```blade
{{-- Your view --}}
@extends('stickle::layouts.default')

@section('title', 'Page Title')

@section('content')
    {{-- Your content here --}}
@endsection

@push('scripts')
    {{-- Additional JavaScript --}}
@endpush
```

## Performance Optimization

### Production Setup

1. **Cache routes:**
   ```bash
   php artisan route:cache
   ```

2. **Build assets:**
   ```bash
   npm run build
   ```

3. **Optimize config:**
   ```bash
   php artisan config:cache
   ```

### Database Optimization

Configure proper caching for segment statistics:

```php
// config/stickle.php
'cache' => [
    'segments' => [
        'ttl' => 3600, // Cache for 1 hour
    ],
],
```

### Queue Workers

Ensure queue workers are running for background processing:

```bash
php artisan queue:work
```

## Common Tasks

### View All Customers

Navigate to `/stickle/user` (or your StickleEntity model)

### See Real-Time Events

Navigate to `/stickle/events`

### Export Segment Members

1. Go to `/stickle/user/segments`
2. Click on a segment
3. Click "Export to CSV"

### Check Customer Health Score

1. Go to `/stickle/user/{id}`
2. View tracked attributes
3. See score history in charts

## Troubleshooting

### Interface Not Loading

- Check middleware configuration in `config/stickle.php`
- Clear route cache: `php artisan route:clear`
- Verify authentication works

### Real-Time Updates Not Working

- Verify Laravel Echo configuration
- Check Reverb/Pusher is running
- Inspect browser console for connection errors

### Missing Data

- Ensure models use `StickleEntity` trait
- Verify migrations are run: `php artisan migrate`
- Check queue workers are processing jobs

### Styling Issues

- Build assets: `npm run build`
- Clear view cache: `php artisan view:clear`
- Check Tailwind configuration includes Stickle views

## Next Steps

- **[Event Listeners](/guide/event-listeners)** - Build workflows that respond to data
- **[API Endpoints](/guide/api-endpoints)** - Access data programmatically
- **[Deployment](/guide/deployment)** - Deploy StickleUI to production
- **[Recipes](/guide/recipes)** - Learn common customization patterns
