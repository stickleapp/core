---
outline: deep
---

# What is Stickle?

Stickle is a customer analytics and engagement package for Laravel that helps you analyze and interact with your customer base. It embeds powerful analytics functionality directly into your Laravel application, giving you complete control over your customer data while providing the tools you need to make data-driven decisions.

Unlike external analytics platforms, Stickle runs entirely within your Laravel application. You own your data, and you can query it using familiar Eloquent methods.

## Key Features

### User Behavior Tracking
Stickle can automatically inject a lightweight JavaScript tracking code to record page views and custom-defined events. Track what your users are doing in real-time without complex integrations.

### Customer Attribute Auditing
Define KPIs as attributes in your models and Stickle will track them over time, enabling powerful filters and historical analysis. Monitor changes to important metrics like MRR, feature usage, or custom business metrics.

### Customer Segment Tracking
Define customer segments 'in-code' using familiar Eloquent syntax and automatically track statistics for each segment over time. Know exactly who your active users, high-value customers, and at-risk accounts are.

### Extended Eloquent Methods
Stickle provides easy-to-use extensions to Eloquent allowing you to create complex filters based on your analytics data. Query users by behavior, attributes, and segment membership using intuitive, chainable methods.

### Real-time Event Orchestration
Respond to events in your application - client or server-side - to build powerful, real-time features. Trigger emails, send notifications, or update external systems when users perform specific actions.

### Pre-built Analytics Dashboard
Stickle comes with a pre-built, zero-dependency dashboard to view your customer analytics. Access insights immediately at `/stickle` without building custom interfaces.

## When to Use Stickle

Stickle is ideal for Laravel applications that need to:

- **Build real-time customer analytics dashboards** - Get insights without external services
- **Trigger notifications based on user behavior** - Respond to what users do in your app
- **Segment customers for targeted engagement** - Identify high-value or at-risk customers automatically
- **Track customer health and engagement** - Monitor metrics that matter to your business
- **Understand product usage patterns** - See which features drive value

### Who are my customers?

After installation, navigate to `/stickle` to see:
- A list of your 'customers' (Laravel models with the `StickleEntity` trait)
- Related models (e.g., Company Users, Admin Users, Parent Company)
- Customer entity details with tracked attributes

### How are people using my app?

Stickle processes your user data and makes it available in charts and data tables:
- Page views and navigation patterns
- Authentication events (login, logout, registration)
- User-defined custom events
- Session analytics

### Segment your customers

If you know Laravel Eloquent, you can build powerful segments:
- Customers who joined this week
- Customers who have paid the most money
- Customers who have been active the longest
- Customers renewing in the next 30, 60, or 90 days
- Your Ideal Customer Profile (ICP)

Once you've built a segment, Stickle automatically tracks KPIs at the segment level (e.g., what is the MRR of customers renewing in the next 30 days).

### Engage with your customers in real-time

Use Stickle with Laravel's built-in features to add real-time functionality:
- Trigger emails when customers haven't logged in recently
- Add new users to your CRM automatically
- Display notifications when users visit specific pages
- Build Intercom-style interactions directly in your Laravel application

## How It Works

Stickle Core provides:

1. **Data Collection** - Tracks user attributes and behavior via JavaScript or server-side events
2. **Event Processing** - Logs authentication events, model attribute changes, and custom events
3. **Query Interface** - Exposes Eloquent methods for querying customer data
4. **REST API** - Ingests events from mobile apps and external channels
5. **Segment Engine** - Defines and tracks customer segments as code
6. **Real-time Broadcasting** - Streams events for instant UI updates

## What Stickle is Not

Stickle isn't a replacement for Google Analytics. It doesn't track anonymous visitors and isn't designed for marketing analytics. Stickle focuses on authenticated customers and helping you understand user behavior after they sign up.

## Next Steps

Ready to get started?

::: tip Quick Start
Follow our [Quick Start Guide](/guide/quick-start) to get Stickle running in your Laravel application in 15 minutes.
:::

Or dive deeper:
- [Installation](/guide/installation) - Detailed installation instructions
- [Configuration](/guide/configuration) - Complete configuration reference
- [Tracking Attributes](/guide/tracking-attributes) - Learn how to track model attributes
- [Customer Segments](/guide/segments) - Build powerful customer segments
