Stickle is a package for Laravel that helps you analyze--and interact with--your customer base.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stickleapp/core.svg?style=flat-square)](https://packagist.org/packages/stickleapp/core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/stickleapp/core/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/stickleapp/core/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/stickleapp/core/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/stickleapp/core/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stickleapp/core.svg?style=flat-square)](https://packagist.org/packages/stickleapp/core)

# Use Cases

Stickle allows you to embed customer analytics and engagement functionality in your Laravel application. Use it to:

-   Build real-time customer analytics dashboard;
-   Trigger Laravel notifications based on end-user behavior;
-   Highlight important customer behavior in your admin panel;
-   Segment your customer base to identify high value or at-risk customers in realtime.

Stickle Apps allow you to do even more.

# Stickle Core

Stickle Core is the foundation of Stickle that:

-   Tracks user attributes and behavior via a Javascript tracking code;
-   Logs authentication events;
-   Logs changes in model attributes;
-   Logs user-defined server-side events;
-   Exposes Eloquent methods for querying your customers;
-   Exposes a REST API for ingesting events from other channels (Mobile, etc);
-   Provides the ability to define customer segments 'as-code' and track these segments over time.

# Stickle Apps

Stickle Apps extend the funtionality of Stickle Core. Anyone can write a Stickle app but we'll provide some first-party apps to get people started.

## Stickle Webhooks

Stickle Webhooks allow you to send Stickle events to a defined Webhook endpoint.

NOTE: Not sure we need this. Is there a definite market winner library for Webhooks? Spatie?

## Stickle Websockets

Built on Laravel Reverb, Stickle Websockets allow you to send notifications to connected clients via Websockets.

NOTE: Not sure we need this. **Maybe** something that works out-of-the-box with the Javasdk?

## Stickle Health

Build customer health scores based on Stickle Core.

## Stickle Orchestrations

Create workflows triggered on data in Stickle Core.

## Stickle Dashboards

Create shareable dashboards containing metrics gleaned from your Customer data.

## Stickle Updates

Share customer-specific email updates to your customers to demonstrate the value they provide.

## Stickle Widget

A multi-purpose, extensible, Intercom-style widget.

# Stickle UI Professional

$499 one time payment includes:

-   A first-party UI for Stickle Core and Stickle Apps;
-   Updates for 1 year;
-   Email support.

Renewals for $99/year.

# Getting Started

## Requirements

Stickle requires:

-   PHP 8.3+
-   Laravel 11.0+.

## Installation

You may use Composer to require Stickle into your PHP project:

    composer require stickleapp/core

You may install Stickle into your Laravel project using the following command:

    php artisan install:stickle

The installer will guide you through the setup process helping you set configuration options for your project. You can specify:

-   If you want to install the Stickle JS SDK and track client events;
-   If you want to track events raised by Illuminate\Auth events;
-   If you want to track each authenticated event via middleware; and
-   How you define the relationships between `Users` and `Groups` in your application.

It will also prompt you to install desired first-party Stickle apps:

-   Stickle Webhooks
-   Stickle Websockets
-   Stickle Health
-   Stickle Orchestrations
-   Stickle Dashboards

## Running Migrations

After you have specified your configuration options in the installation script, run migrations:

    php artisan migrate

## Initialization

When complete you can run Stickle with the following command:

    php artisan run:stickle

This command will does the following:

-   activates cron tasks; `php artisan schedule:work`
-   activates websockets server (if applicable).

You can access a test page at the URL specified in the terminal.

# Testing

`vendor/bin/testbench vendor:publish --force`

`vendor/bin/testbench workbench:build`

`vendor/bin/testbench migrate:refresh`

`vendor/bin/testbench db:seed --class=\\Workbench\\Database\\Seeders\\DatabaseSeeder`

`vendor/bin/testbench migrate:fresh --seed --seeder=\\Workbench\\Database\\Seeders\\DatabaseSeeder`

`vendor/bin/testbench stickle:record-model-attributes ~/Projects/StickleApp/Core/workbench/app/Models \\Workbench\\App\\Models 10`

`vendor/bin/testbench stickle:export-segments ~/Projects/StickleApp/Core/workbench/app/Segments \\Workbench\\App\\Segments 10`

`vendor/bin/testbench stickle:record-segment-statitics`

'vendor/bin/testbench stickle:record-model-statitics'

`vendor/bin/testbench stickle:process-segment-events`

`./vendor/bin/testbench workbench:send-test-requests`

files
`vendor/orchestra/testbench-core/laravel/storage/app` (CSV temp exports)
`tail -f vendor/orchestra/testbench-core/laravel/storage/logs/laravel.log` (Logs)

# Advanced Configuration

Stickle will work out of the box using the configuration options specified during the installation process. You can override these (and several other) options in the `config\stickle.php` file.

## Database Options

You can specify the following configuration options:

-   `connection`. (Defaults to `DB_CONNECTION` env value) This connection should contain the tracked models.
-   `tablePrefix`. (Default `lc*`) This will be prepended to the tables created by the migrations.

## Sync Schedule

Stickle runs several jobs to transform your data. You can update the frequency that these jobs run:

-   `ExportSegments`. (Default Every 360 minutes).
-   `RecordSegmentStatistics`. (Default Every 360 minutes).
-   `RecordEntityStatistics`. (Default Every 360 minutes).
-   `RollupEvents`. (Default Every 360 minutes).
-   `RollupPageViews`. (Default Every 360 minutes).
-   `RollupSessions`. (Default Every 360 minutes).

## Customer Models

-   `User`. By default, Stickle assumes the `App\Models\User` class is the user responsible for authentication events. You can override this class.
-   `Group`. You can **optionally** specify a Group class that represents a real world company, account, organization, etc. that a `User` belongs to.
-   `Relationship`. You should specify the relationship between the `Group` and the `User`. By default, the relationship is `Illuminate\Database\Eloquent\Relations\HasMany` meaning each `Group` has zero or more `Users` objects. Other options include:
    -   `Illuminate\Database\Eloquent\Relations\HasOne`. Each `User` belongs to one `Group` and each `Group` has exactly one `User`.
    -   `Illuminate\Database\Eloquent\Relations\BelongsToMany`. Each `User` can belong to zero or more `Groups` and each `Group` can have have zero or more `Users`.
-   `Payment`. You can **optionally** specify a Payment model that represents a payment made by a StickleEntity model to your organization.

## Tracking Options

Stickle can track requests and events on the server and on the client. There are several configuration options that determine the behavior of each method:

### Server

-   `loadMiddleware`. Default `true`. When `true`, Stickle loads middleware during the Package Service Provider's `boot` method.
-   `trackAuthenticationEvents`. Default `true`. Automatically log all authenticated `Illuminate\Auth` events.
-   `trackRequests`. Default `true`. Automatically track all authenticated requests made in the application.

### Client

-   `loadMiddleware`. Default `true`. When `true`, Stickle injects a tracking code via middleware during the Package Service Provider's `boot` method.
-   `trackPageViews`. Default `true`. When `true`, the Javascript tracking code will listen for pushState changes and record a pageview for each.
-   `routePrefix`. Default `''`. Tracking events are sent to the `/request` endpoint by default. If you move this endpoint to a different location, update `routePrefix` to reflect this location.

## Paths

Where to autoload `Segments` (ActiveAccounts.php) and export them.
Where to autoload `Listeners`
Where to autoload `Playbooks` (ActiveAccounts.php) and export them (Move to => Stickle Playbooks)

# How To

OK. So you installed Stickle and typed the `php artisan stickle:run` command. You were given a URL to open in your web browser (probably http://localhost:8000). You should see a demo screen that you can use to test some things out.

The left frame is your application, the middle frame is an example 'admin console' and the right frame is an example 3rd-party application like a CRM.

```
Add echo code to your app
```

```
Add echo code to admin
```

```
Code to send a webhook to fake 3rd party service http://localhost:9000
```

Start by logging into your application in the left frame. You should have seen:

-   Register a new user

-   A welcome message on the login page;
-   Some updates in the admin panel:

    -   An alert announcing that someone just logged in;
    -   An update to the 'Logged In Users' widget;
    -   An update to your row in the Active Users table (with a new Login Count and Most recent login);

-   In the admin frame, click on your username
    In the application frame, update your username and add your birthday and click 'Save'. You should see:

-   In application:
-   In admin panel:

Now do some stuff in the admin interface...

Now do some stuff in the CRM...

```
Call a remote webhook
```

Meanwhile, Stickle is refreshing your Segments:

-   Added to Segment alert in Admin

## Tracking End User Behavior

### On the Server

```
use StickleApp\Core\Events\Track;
use StickleApp\Core\Events\Group;

Track::dispatch(...args);
Group::dispatch(...args);
Identify::dispatch(...args);
```

### On the Browser

```
Stickle('track', []);
Stickle('identify', []);
Stickle('page', []);
Stickle('group', []);
```

## Tracking Model Attribute Changes

In your StickleEntity model, you can have Stickle track numerical attributes over time. To do so, add the attribute to the `$tracked` array on the model.

## Eloquent Models

Stickle exposes two Eloquent scopes that allow you to segment your users based on their behavior or history: `stickle` and `orStickle`.

These methods expect an instance of a `StickleApp\Core\Filter` class.

```
use App\Models\User;
use StickleApp\Core\Filters\Base as Filter;

$users = User::stickle(
        Filter::eventCount('clicked:something')
            ->greaterThan(10)
            ->onOrAfter(now()->subYears(1))
            ->before(now()))
    ->get();
```

```
use App\Models\User;
use StickleApp\Core\Filters\Base as Filter;

$users = User::stickle(
        Filter::eventCount('clicked:something')
            ->increased(
                [now()->subYears(2), now()->subYears(1)],
                [now()->subYears(1), now()],
            )
            ->moreThan(new Percentage(10));
    )->get();
```

Please see [docs/ELOQUENT](docs/ELOQUENT.md) for a full list of filters and options.

## Creating Segments

You can create segments of StickleEntity models by creating a class that implements the `Segment` contract and placing it in the `App\Segments` directory.

These classes contain the definition of the segment, most notably, a method that returns a `Illuminate\Database\Eloquent\Builder` that returns the items contained in the Segment.

| Method / Attribute | Description                                                              | Required | Data Type | Default |
| ------------------ | ------------------------------------------------------------------------ | -------- | --------- | ------- |
| name               | Human readable name                                                      | Y        | String    |         |
| refreshInterval    | How frequently the segment is refreshed (minutes)                        | Y        | Integer   | 360     |
| class              | The StickleEntity model class (ex. App\Models\User)                      | Y        | String    |         |
| builder()          | An Eloquent builder class that specifies the filters forming the segment | Y        | Builder   |         |

## Reacting to Events

### User Events

### TrackEvents

Creates a listener in App\Listeners\User\NameOfEventListener and it will be called when the event is received.

### TrackPageView

Define routes that you want to handle in config(). You can use the same patterns as Laravel's routing. You can assign Listeners to be called when a page is visited.

```
config\stickle.php

$pageViewListenders => [
    '/upgrade' => [
        \App\Listeners\SendUpgradeOfferEmail::class,
    ]
],

```

### Server Events

Why handle these through Stickle? We want to log them? We want to hook into webhooks/websockets?

We'll generate some around Segments? What else?

We'll dispatch some TrackEvents for:
EnteredSegment
ExitedSegment

# Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

# Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

# Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

# Credits

-   [D Clay Smith](https://github.com/dclaysmith)
-   [All Contributors](../../contributors)

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

    			<!--
    			<x-segment-table
    				title="Active Users"
    				segment="Active Users"
    				columns="['name','email']"
    				per-page="[10,25,50]" />
    			<x-segment-chart
    				title="New Users"
    				segment="NewUsers"
    				metric="count"
    				aggregates="['count']"
    				start-date=""
    				end-date=""
    				increment="['day','week']" />
    			<x-segment-chart
    				title="New User MRR"
    				segment="NewUsers"
    				metric="mrr"
    				aggregates="['sum', 'avg', 'min', 'max']"
    				start-date=""
    				end-date=""
    				increment="['day','week']" />

-->

# StickleApp Core

This document is to provide context to an AI agent before answering questions.

## Project Overview

StickleApp is a customer analytics and engagement package for Laravel that helps developers track user behavior and attributes, analyze customer data (individually and as 'segments' of customers), and increase engagement between customers and the application.

## Key Features

-   **User Behavior Tracking**: Tracks pageviews and custom events
-   **Customer Attribute Auditing**: Tracks model attributes over time (`model_attribute_audit`)
-   **Customer Statistic Tracking**: Tracks aggregates for object children--for instance users that belong to a customer or customers with child customer objects ('ACME Global and ACME US') (`object_statistics`)
-   **Customer Segment Tracking**: Define segments in code and track segment statistics over time (`segment_statistics`)
-   **Event Coordination**: Listen for and trigger events and broadcast them to web users via websockets.
-   **Customer Analytics Reporting**: Stickle provides a reporting UI for viewing user events and KPIs.

## Architecture

StickleApp Core follows a Laravel-centric architecture with:

-   **Models**: Represent data entities like segments, user attributes
-   **Events & Listeners**: Event-driven system for tracking users, identifying changes in user attributes and existence in segments
-   **Commands**: Background processes for analytics and data transformations
-   **Middleware**: HTTP middleware for: - tracking requests; - injecting tracking code; and - authenticating users
-   **Eloquent Scopes**: Extend Eloquent queries with custom filtering logic
-   **Routes**: Stickle declares routes in 3 files (`api.php`, `web.php`, `channels.php`)

## Data Storage

-   **Primary Data Store**: At the moment, Stickle supports only Postgres.
-   **JSONB Columns**: Leverages JSON columns for flexible attribute storage
-   **Table Partitioning**: Stickle uses time-based partitioning for events, requests, and statistics tables to maintain performance with large datasets.
-   **Roll-up Tables**: Stickle implements multi-interval data aggregation (1min, 5min, 1hr, 1day) with incremental updates for efficient querying of time-series data

## Extension Points

Extension points are elements of a software system designed to allow developers to extend or customize the functionality without modifying the core code. They're intentional interfaces or mechanisms where the system is designed to be extended.

Extension points for StickleApp / Core include:

-   **Models**: Models (typically `User`) can be assigned the `StickleEntity` trait. This enables developers to use custom filters via a Eloquent scopes (`scopeStickle` and `orScopeStickle`)
-   **Custom segments**: Developers can create extend the `StickleApp\Core\Contracts\SegmentContract` abstract class to create Segments which are subsets of models with the `StickleEntity` trait. They can use standard Eloquent filters or Stickle filters (`StickleApp\Core\Filters`) appended to Eloquent builders using the added scopes (`scopeStickle` and `orScopeStickle`).
-   **Segment Event Listeners**: Developers can listen for when models enter and exit segments and trigger actions.
-   **Custom attribute listeners**: Developers can listen for changes to specific attributes defined in the `stickleObservedAttributes` attribute of `StickleEntity` models. This is done by creating a Laravel Listener with a specific naming structure `{ModelName}{AttributeName}Listener` in the configured listeners namespace.
-   **Custom event listeners**: Similar to attribute listeners, developers can create classes like `{EventName}Listener` to respond to custom events tracked via the JavaScript SDK or server-side tracking. These let developers create custom business logic in response to user actions.
-   **Re-usable components**: Stickle provides Blade components that Developers can re-use in admin panels. These include Model and Segment charts and lists. They have few dependencies that can be loaded using external JS files hosted on a CDN (Alpine.js, Chart.js, Simple Datatables, Pusher, Echo)

## UI Components

StickleApp includes a growing set of UI components for building analytics dashboards:

-   **Charts**: Line and table charts to display model and segment metrics over time
-   **Tables**: Interactive data tables for displaying segment members and statistics
-   **Timelines**: Real-time event streams for monitoring user activity individually and within segments

# StickleApp UI Implementation To-Do List

## Live Dashboard View (`/stickle/live`)

The Live Dashboard View shows users that are currently active at the moment.

`/resources/views/pages/live.blade.php`

-   [ ] Create ActiveUserTable component to display currently active users
    -   Show user names, avatars, last page/activity
    -   Include last activity timestamp
    -   Add sorting and filtering options
-   [ ] Enhance EventsTimeline component for real-time pageview and event display
    -   Improve event formatting and grouping
    -   Add filtering by:
        -   Page Views or Event Types
        -   Page or Event Contains Text
    -   Implement auto-scrolling with pause option
-   [ ] Develop UserLocationMap component
    -   Create SVG world map with user location markers
    -   Show concentration heat zones for multiple users in same area
    -   Include country/region summary statistics
-   [ ] Build ActivityBarChart component
    -   Display hourly active user counts
    -   Add time period selectors (1h, 6h, 24h, 7d)
    -   Implement real-time updates via broadcasting

## StickleEntity Model Index Views (`/stickle/{class-basename}` ie. `/stickle/users`)

`/resources/views/pages/models.blade.php`

-   [ ] Create EntityIndexController to discover and list StickleEntity models
    -   Auto-detect models using StickleEntity trait
    -   Configure routing based on discovered models
-   [ ] Develop EntityTable component for model listing
    -   Show count of total objects
    -   Display key metrics (average values, trends)
    -   Include action buttons (view details, segments)
-   [ ] Implement EntityMetricsPanel component
    -   Show summary statistics for all observed attributes
    -   Include trend indicators (up/down from previous period)

## Segment Management Views (`/stickle/{class-basename}/segments` ie. `/stickle/users/segments`)

`/resources/views/pages/segments.blade.php`

-   [ ] Create SegmentListComponent to display available segments
    -   Show object count and key metrics per segment
    -   Include last refresh timestamp

## Segment Detail View (`/stickle/{class-basename}/segments/{segmentId}` ie. `/stickle/users/segments/12`)

`/resources/views/pages/segment.blade.php`

-   [ ] Develop SegmentDetailView
    -   [ ] Implement ObjectListComponent to show segment members
        -   Paginated table of objects in segment
        -   Quick filters and sorting options
        -   Export functionality
    -   [ ] Create AttributeChartsPanel component
        -   Generate chart for each observed attribute
        -   Support multiple visualization types (line, bar, etc.)
        -   Implement metric type toggle (MIN/MAX/SUM/AVG)
    -   [ ] Add ChartModalComponent
        -   Full-screen detailed view of charts
        -   Additional filtering and date range options
        -   Download chart as image/data

## Entity Detail Views ('/stickle/{class-basename}/{objectUid}')

`/resources/views/pages/model.blade.php`

-   [ ] Create EntityDetailController and routes
    -   Support viewing any StickleEntity model
    -   Handle relationship navigation
-   [ ] Develop EntityAttributesPanel component
    -   Display current values for all observed attributes
    -   Show change indicators (vs previous period)
    -   Include last updated timestamp per attribute
-   [ ] Implement AttributeHistoryCharts component
    -   Individual chart for each observed attribute
    -   Time range selector (7d, 30d, 90d, 1y, all)
    -   Metric type selector (raw, min, max, avg, sum)
-   [ ] Create RelationshipNavigator component
    -   List child objects with key metrics
    -   Pagination and filtering for children
    -   Link to parent object with summary info

## Common Components and Infrastructure

-   [ ] Improve UI layout system
    -   Responsive grid for all views
    -   Consistent card styling and headers
    -   Dark/light mode support
-   [ ] Enhance data fetching layer
    -   Create API endpoints for all required data
    -   Implement caching for performance
    -   Add polling/real-time updates where appropriate
-   [ ] Develop filtering and date range system
    -   Consistent date picker component
    -   Presets for common time periods
    -   Custom range selection
-   [ ] Create documentation and examples
    -   Document all available components
    -   Provide example implementations
    -   Include customization options
-   [ ] Implement customizabl security (as middleware?) to control access to UI
-   [ ] Implement a search functionality
    -   Use fulltext searching or meliasearch?

## Integration with Laravel Echo and Broadcasting

-   [ ] Configure Echo client for real-time updates
    -   Set up proper authentication
    -   Handle reconnection gracefully
-   [ ] Create broadcast events for entity changes
    -   Broadcast attribute updates
    -   Broadcast segment membership changes
-   [ ] Implement listeners for UI components
    -   Update charts and tables in real-time
    -   Show notifications for important events

## Chart Components

The following items are generated by Claude and are not yet accepted/adopted.

-   [ ] Develop TimeSeriesChart component

    -   Support for multiple series overlay
    -   Configurable time intervals (hourly, daily, weekly, monthly)
    -   Interactive tooltips with point-in-time values
    -   Zoom and pan functionality for exploring data
    -   Export options (PNG, CSV)

-   [ ] Create AttributeDistributionChart component

    -   Display value distributions across segments
    -   Support for various chart types (histogram, pie, bar)
    -   Percentile indicators and outlier highlighting
    -   Dynamic filtering of data ranges

-   [ ] Implement SegmentComparisonChart component

    -   Side-by-side visualization of multiple segments
    -   Normalize data for fair comparison
    -   Highlight statistical significance of differences
    -   Support for switching metrics without reloading

-   [ ] Build RealTimeMetricChart component

    -   Auto-updating without full page refresh
    -   Configurable refresh intervals
    -   Smooth animations for transitions
    -   Historical context with focus on recent data
    -   Pause/resume live updates option

-   [ ] Develop KpiSummaryChart component

    -   At-a-glance visualization of key metrics
    -   Trend indicators (up/down arrows)
    -   Percentage change calculations
    -   Customizable thresholds for warnings/alerts

-   [ ] Create CohortRetentionChart component
    -   Display user retention over multiple time periods
    -   Heat map visualization for cohort analysis
    -   Filterable by segment or attribute value
    -   Export functionality for further analysis

## Usage Examples

See documentation for detailed usage examples (/docs).

## Licensing

StickleCore is MIT licensed.
