# StickleApp Core

This document is to provide context to an AI agent before answering questions.

## Project Overview

StickleApp is a customer analytics and engagement package for Laravel that helps developers track user behavior and attributes, analyze customer data (individually and as 'segments' of customers), and increase engagement between customers and the application.

## Key Features

-   **User Behavior Tracking**: Tracks pageviews and custom events
-   **Customer Attribute Auditing**: Tracks model attributes over time (`object_attributes_audit`)
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
