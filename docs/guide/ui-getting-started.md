# StickleUI Getting Started Guide

StickleUI is the web interface for Stickle that provides a comprehensive dashboard for viewing customer analytics, segments, and model data. This guide will help you get started with using and understanding the StickleUI interface.

## Overview

StickleUI provides a modern, responsive web interface built with Laravel Blade templates, Tailwind CSS, Alpine.js, and Chart.js. It offers real-time analytics visualization and interactive data exploration capabilities.

## Accessing StickleUI

By default, StickleUI is accessible at `/stickle` from your Laravel application root. The interface is protected by configurable middleware (defaults to `web` middleware).

**Base URL Structure:**
```
https://yourapp.com/stickle/
```

## Main Interface Components

### 1. Dashboard (Live View)
- **URL:** `/stickle/` or `/stickle/live`
- **Purpose:** Main dashboard showing live analytics and real-time data
- **Features:** Real-time updates via Laravel Echo and Pusher integration

### 2. Model Management
- **URL Pattern:** `/stickle/{modelClass}`
- **Purpose:** View all instances of a specific model (Users, Customers, etc.)
- **Example:** `/stickle/user` - Shows all users with analytics data

### 3. Individual Model View
- **URL Pattern:** `/stickle/{modelClass}/{uid}`
- **Purpose:** Detailed view of a specific model instance
- **Features:** 
  - Model attributes timeline
  - Event history
  - Relationship data
  - Interactive charts

### 4. Model Relationships
- **URL Pattern:** `/stickle/{modelClass}/{uid}/{relationship}`
- **Purpose:** Explore relationships between models
- **Features:** Relationship statistics and visualizations

### 5. Segments Management
- **URL Pattern:** `/stickle/{modelClass}/segments`
- **Purpose:** View and manage customer segments for a model
- **Features:**
  - Segment listing with statistics
  - Real-time segment population counts
  - Segment performance metrics

### 6. Individual Segment View
- **URL Pattern:** `/stickle/{modelClass}/segments/{segmentId}`
- **Purpose:** Detailed analysis of a specific segment
- **Features:**
  - Segment member lists
  - Historical performance data
  - Export capabilities

## Key Features

### Navigation Structure
- **Sidebar Navigation:** Access to different model types and their segments
- **Breadcrumb Navigation:** Clear path indication for current location
- **Responsive Design:** Mobile-friendly interface with collapsible sidebar

### Real-time Updates
StickleUI includes built-in real-time capabilities using:
- **Laravel Echo** for WebSocket connections
- **Pusher/Reverb** for broadcasting events
- **Automatic UI updates** when data changes

### Interactive Components
- **Data Tables:** Sortable, searchable, paginated tables with Simple-DataTables
- **Charts:** Interactive visualizations using Chart.js
- **Tabs:** Responsive tab interface for organizing content
- **Filters:** Dynamic filtering capabilities for segments and data

### Data Visualization
- **Line Charts:** Trend analysis over time
- **Delta Indicators:** Change metrics with visual indicators
- **Statistics Cards:** Key metric summaries
- **Timeline Views:** Event chronology display

## Demo Environment

StickleUI includes a comprehensive demo environment accessible at:

### Demo Routes
- `/stickle/demo` - Main demo interface with side-by-side views
- `/stickle/app` - Sample application interface
- `/stickle/integration` - Integration examples
- `/stickle/admin` - Administrative interface demo

The demo provides:
- Live application simulation
- Administrative interface preview
- StickleUI interface demonstration
- Integration examples

## Technical Architecture

### Frontend Stack
- **Blade Templates** - Server-side rendering
- **Tailwind CSS** - Utility-first styling
- **Alpine.js** - Reactive JavaScript framework
- **Chart.js** - Data visualization
- **Simple-DataTables** - Table functionality

### Layout Structure
- **Default Layout:** `resources/views/components/ui/layouts/default-layout.blade.php`
- **Responsive Design:** Mobile-first approach with collapsible sidebar
- **Component Architecture:** Reusable Blade components for consistency

### Page Components
- **Tables:** `resources/views/components/ui/tables/`
- **Charts:** `resources/views/components/ui/charts/`
- **Partials:** `resources/views/components/ui/partials/`
- **Layouts:** `resources/views/components/ui/layouts/`

## Getting Started Steps

1. **Ensure Stickle is Configured**
   ```bash
   php artisan stickle:configure
   ```

2. **Run Migrations**
   ```bash
   php artisan migrate
   ```

3. **Start Frontend Development** (if customizing)
   ```bash
   npm install
   npm run dev
   ```

4. **Access the Interface**
   - Navigate to `https://yourapp.com/stickle/`
   - Explore the demo at `https://yourapp.com/stickle/demo`

5. **Configure Models**
   - Ensure your models use the `StickleEntity` trait
   - Define segments in `workbench/app/Segments/`

## Customization

### Theming
StickleUI uses Tailwind CSS and can be customized by:
- Modifying the Tailwind configuration
- Overriding component templates
- Customizing the default layout

### Adding Custom Views
Create custom pages by:
1. Adding routes to `routes/web.php`
2. Creating corresponding Blade templates
3. Using existing UI components for consistency

### Configuration Options
Configure StickleUI behavior in `config/stickle.php`:
- Route prefixes and middleware
- Model namespaces
- UI-specific settings

## Troubleshooting

### Common Issues
1. **Interface not loading:** Check middleware configuration and route caching
2. **Real-time updates not working:** Verify Echo/Pusher configuration
3. **Missing data:** Ensure models are using StickleEntity trait and migrations are run
4. **Styling issues:** Verify Vite assets are built (`npm run build`)

### Performance Optimization
- Use Laravel's route caching for production
- Optimize database queries for large datasets
- Configure proper caching for segment statistics
- Use queue workers for background processing

This interface provides a powerful way to visualize and interact with your Stickle analytics data, offering both high-level overviews and detailed drill-down capabilities for comprehensive customer analytics.