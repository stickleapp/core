# Stickle Live Dashboard Implementation Plan

## 🎯 Overview

Transform the `/stickle/live` route into a comprehensive real-time analytics dashboard with:

-   Interactive map showing recent visitor locations with circles
-   Activity chart that overlays the Interactive map showing:
    -   Active users in last 30 minutes
    -   Active users in last 5 minutes
    -   Active users per minute over last 30 minutes (bar chart)
-   Active users list (left column) with click-to-view details
-   User details panel (right column) with event timeline

## 🏗️ Technical Architecture

### Backend Components

1. **API Endpoints** - Optimized endpoints based on dashboard component needs

    **ActivitiesController** (`/stickle/api/activities`)

    - **Purpose**: Raw activity/event stream for user list and general activity feeds
    - **Filters Required**:
        - `model_class` - Model class name (e.g., "User", "Customer") (optional)
        - `object_uid` - Model instance ID (e.g., 234) (optional)
        - `start_at` - Start datetime filter (default: 30 minutes ago)
        - `end_at` - End datetime filter (default: now)
        - `event_types` - `page_view|event` (optional)
        - `limit` - Number of recent activities (default: 50)
        - `include_location` - `true|false` (default: true)
    - **Server-side Processing**:
        - Union of requests and events tables with datetime filtering
        - Join location_data when `include_location=true`
        - Latest activity per user using window functions
    - **Attributes Returned**:
        ```json
        {
            "data": [
                {
                    "id": 123,
                    "model": {
                        "name": "John Doe",
                        "email": "john@example.com",
                        "customer_name": "Acme Corp",
                        "user_type": "END_USER"
                    },
                    "activity_type": "page_view",
                    "properties": {
                        "url": "/some/page",
                        "event": "clicke:me"
                    },
                    "location": {
                        "city": "New York",
                        "country": "USA",
                        "lat": 40.7128,
                        "lng": -74.006
                    },
                    "session_status": "active" // Last activity by user within 30 minutes?
                }
            ]
        }
        ```

    **ActivityStatisticsController** - Multiple focused endpoints:

    **`/stickle/api/activity-statistics/by-city`** (Map Component)

    - **Purpose**: Geographic aggregation for map markers
    - **Filters Required**:
        - `start_at` - Start datetime filter (default: 30 minutes ago)
        - `end_at` - End datetime filter (default: now)
        - `event_types` - `page_view|event` (optional)
        - `location_filter` - `{city},{country}` (optional geographic filtering)
    - **Server-side Processing**: `GROUP BY city, country` with location_data joined
    - **Attributes Returned**:

        ```json
        {
            "data": [
                {
                    "location": {
                        "city": "New York",
                        "country": "USA",
                        "lat": 40.7128,
                        "lng": -74.006
                    },
                    "active_count": 15,
                    "activity_level": "high"
                }
            ]
        }
        ```

    **`/stickle/api/activity-statistics/by-minute`** (Activity Charts)

    - **Purpose**: Time-bucketed aggregation for charts
    - **Filters Required**:
        - `start_at` - Start datetime filter (default: 30 minutes ago)
        - `end_at` - End datetime filter (default: now)
        - `model_class` - Model class name (optional)
    - **Server-side Processing**: `GROUP BY date_trunc('minute', timestamp)`
    - **Attributes Returned**:
        ```json
        {
            "data": [{ "minute": "2024-01-01T10:45:00", "count": 3 }]
        }
        ```

    **ModelsController** (`/stickle/api/models`)

    - **Purpose**: Comprehensive model data for details panel (supports any StickleEntity model)
    - **Filters Required**:
        - `model_class` - Model class name (e.g., "User", "Customer")
        - `object_uid` - Model instance ID (e.g., 234)
    - **Server-side Processing**:
        - Dynamic model resolution: `config('stickle.namespaces.models') . '\\' . ucfirst($model_class)`
        - Load model relationships and Stickle tracked attributes
        - Join current session location data
    - **Attributes Returned**:
        ```json
        {
            "id": 234,
            "name": "John Doe",
            "email": "john@example.com",
            "user_type": "END_USER",
            "created_at": "2023-06-15T09:30:00",
            "customer": { "id": 45, "name": "Acme Corp" },
            "current_location": { "city": "New York", "country": "USA" },
            "session": {
                "status": "active",
                "last_activity": "30s ago",
                "duration": "5m 23s"
            },
            "metrics": {
                "user_rating": 4.2,
                "ticket_count": 15,
                "resolved_count": 12,
                "tickets_resolved_last_30_days": 8
            }
        }
        ```

    **ModelActivitiesController** (`/stickle/api/model-activities`)

    - **Purpose**: Paginated activity timeline for events timeline component (supports any StickleEntity model)
    - **Filters Required**:
        - `model_class` - Model class name (e.g., "User", "Customer")
        - `object_uid` - Model instance ID (e.g., 234)
        - `page` - pagination (default: 1)
        - `per_page` - results per page (default: 25, max: 100)
        - `event_types` - `page_view|auth|track|attribute_change` (optional filtering)
        - `start_at`, `end_at` - datetime range filtering (optional)
    - **Server-side Processing**:
        - Union of requests and events tables filtered by `model_class` and `object_uid`
        - Order by timestamp DESC for reverse chronological
        - Join location_data for geographic context
        - Parse event details based on event type
    - **Attributes Returned**:
        ```json
        {
            "data": [
                {
                    "id": "req_456",
                    "type": "page_view",
                    "timestamp": "2024-01-01T10:45:23",
                    "model_name": "John Doe",
                    "parent_entity": "Acme Corp",
                    "details": {
                        "url": "/dashboard/analytics",
                        "referrer": "/login"
                    },
                    "location": { "city": "New York", "country": "USA" }
                },
                {
                    "id": "evt_789",
                    "type": "auth",
                    "timestamp": "2024-01-01T10:30:15",
                    "model_name": "John Doe",
                    "parent_entity": "Acme Corp",
                    "details": { "event": "Login", "ip": "192.168.1.1" },
                    "location": { "city": "New York", "country": "USA" }
                }
            ],
            "pagination": {
                "current_page": 1,
                "per_page": 25,
                "total": 147,
                "last_page": 6
            }
        }
        ```

2. **Location Tracking** - New database architecture for IP geolocation
    - **location_data table** - `ip_address`, `country`, `city`, `latitude`, `longitude`
    - **IP columns** - Add `ip_address` to existing `requests` and `events` tables
    - **Geolocation service** - Integration with ipapi.co for location lookup during ingestion
3. **WebSocket Events** - Real-time updates via existing Echo/Reverb setup
    - **Firehose Channel** (`stickle.firehose`) - Global stream of all new events/activities for dashboard-wide updates
        - New visitor locations for map updates
        - Activity data for chart updates
        - Active user session changes
    - **Object Channels** (`stickle.object.{modelClass}.{objectUid}`) - User-specific channels when individual user is selected
        - Detailed event timeline for selected user
        - Real-time attribute changes for selected user
        - Authentication events for selected user

### Frontend Components

1. **Map Component** - Interactive world map using Leaflet.js
    - **City-level Visualization** - Single marker per city (not individual users) for performance
    - **Dynamic Marker Sizing** - Marker size represents number of active users in that city
    - **Color-coded Activity** - Marker color intensity shows activity level (new vs idle sessions)
    - **Hover Tooltips** - City name, country, and current active user count on mouseover
    - **Zoom & Pan** - Full map navigation with smooth transitions
    - **Geographic Filtering** - Click city marker to filter entire dashboard by that location
    - **Real-time Animation** - New sessions cause markers to pulse/grow via firehose channel
    - **Data Structure** - Backend aggregates sessions by city: `{city, country, lat, lng, active_count, activity_level}`
2. **Activity Chart** - Multiple time-window charts using existing Chart.js
    - **Summary Cards** - "ACTIVE USERS IN LAST 5 MINUTES" and "ACTIVE USERS IN LAST 30 MINUTES" with large numbers
    - **Per-Minute Bar Chart** - "ACTIVE USERS PER MINUTE" showing 1-minute intervals over 30-minute window
    - **Real-time Updates** - Latest minute updates via firehose channel with smooth bar animations
    - **Data Bucketing** - Backend uses `date_trunc('minute', timestamp)` for efficient 1-minute time intervals
    - **API Extension** - Extends `ActivitiesController` with `interval` parameter (`1m`, `5m`, `30m`) and `bucket_size`
    - **Dual Layout** - Can display as map overlay cards or standalone dashboard components
    - **Performance Optimization** - Uses map/reduce functions on existing session/event data, no additional endpoints needed
3. **User List** - Left sidebar with active users, sorted by most recent activity
    - **Recent Activity Sorting** - Users with most recent events appear at top, sorted by latest timestamp
    - **API Reuse** - Extends `RecentSessionsController` with `group_by=user` to return user-centric rather than session-centric data
    - **User Information Display** - Avatar/initials, name, "last seen 30s ago" timestamps, activity type badges
    - **Geographic Context** - Shows user location (city, country) from joined location_data
    - **Session Status Indicators** - Active/idle status based on activity recency
    - **Real-time Reordering** - Firehose channel updates cause users to jump to top when new events occur
    - **Click Selection** - Alpine.js click handlers to select user and load details panel
    - **Performance Optimization** - Uses window functions for efficient latest-activity-per-user queries
4. **User Details Panel** - Right column showing comprehensive selected user information
    - **Primary Information**
        - **Name** with link to full user profile (`/stickle/user/{id}`)
        - **Parent Entity** (Customer) with link to customer profile (`/stickle/customer/{id}`)
        - **Date Added** (user created_at timestamp)
        - **User Type** badge (END_USER, AGENT, ADMIN from enum)
    - **Session Context**
        - **Current Location** (city, country from joined location_data)
        - **Session Status** (active/idle indicator based on last activity)
        - **Last Activity** ("30 seconds ago" with activity type)
        - **Session Duration** (time since first activity in current session)
    - **Key Metrics** (from Stickle tracked attributes)
        - **User Rating** (if applicable for agents)
        - **Ticket Counts** (assigned, resolved, open)
        - **Recent Activity Stats** (tickets resolved last 7/30 days)
    - **API Integration** - Uses existing `ModelsController.show()` with user model and Stickle attribute loading
    - **Real-time Updates** - Subscribes to user's object channel for live metric updates
5. **Events Timeline** - Chronological activity feed for selected user
    - **Event Display** - Reverse chronological list (most recent at top) showing user's complete activity stream
    - **Event Information Per Entry**
        - **User Name** (parsed from User.name field since no separate first/last names)
        - **Parent Entity** (Customer name with contextual link)
        - **Timestamp** (relative time: "2 minutes ago" with exact time on hover)
        - **Event Details** based on type:
            - **Page Views**: "Visited `/dashboard/analytics`"
            - **Authentication**: "Logged in", "Logged out", "Password reset"
            - **Custom Track Events**: "Clicked CTA Button", "Downloaded Report"
            - **Attribute Changes**: "User rating changed from 4.2 to 4.5"
    - **Visual Design**
        - **Event Type Icons** - Different icons/colors for page views, auth events, custom events
        - **Infinite Scroll** - Paginated loading for performance with large event histories
        - **Auto-scroll Lock** - Stops auto-scrolling when user manually scrolls up
    - **API Integration** - Uses `ActivitiesController` with filters: `model_class=User&object_uid={userId}&sort=desc`
    - **Real-time Updates** - Object channel subscription prepends new events to top of timeline

## 🛠️ Implementation Steps

### Phase 1: Core Infrastructure

-   Create `location_data` migration with `ip_address`, `country`, `city`, `latitude`, `longitude` columns
-   Add `ip_address` columns to existing `requests` and `events` tables
-   Integrate IP geolocation service (ipapi.co) into tracking system for location lookup during ingestion
-   Build `RecentSessionsController` and `ActivitiesController` with proper request validation

### Phase 2: Dashboard Layout

-   Replace placeholder `live.blade.php` with comprehensive dashboard layout
-   Implement responsive grid: full-width map card, two-column user interface
-   Add Leaflet.js for interactive mapping capabilities

### Phase 3: Interactive Components

-   Build map component with visitor location circles (size = activity level)
-   Create overlay bar chart showing user activity in 30-minute intervals
-   Implement active users list with click handlers
-   Build user details panel that updates on selection
-   Create events timeline component with real-time updates

### Phase 4: Real-time Features

-   **Firehose Channel Integration**
    -   Map component subscribes for new visitor locations and updates circles in real-time
    -   Activity chart subscribes for new activity data and updates bars dynamically
    -   User list subscribes for active session changes and updates user status
-   **Object Channel Integration**
    -   User details panel subscribes to selected user's channel when user is clicked
    -   Events timeline receives real-time events for selected user
    -   Dynamic subscription management (unsubscribe from previous user, subscribe to new user)
-   **Channel Broadcasting**
    -   Broadcast to firehose when new requests/events are ingested
    -   Broadcast to specific object channels when user-specific events occur

## 📊 Data Requirements

-   Recent user sessions (last 30 minutes)
-   User geolocation data (city/country level)
-   Page view events and custom tracking events
-   User authentication events
-   Model attribute changes per user

## 🎨 UI/UX Features

-   Responsive design (mobile-friendly)
-   Smooth animations and transitions
-   Color-coded activity levels on map
-   Hover states and tooltips
-   Loading states for data fetching

The plan leverages your existing infrastructure (TailwindCSS, Alpine.js, Chart.js, Echo/Reverb) while adding minimal new dependencies (Leaflet.js for mapping).
