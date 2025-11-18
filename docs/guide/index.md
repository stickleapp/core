---
outline: deep
---

# What is Stickle?

Stickle is a customer analytics and engagement package for Laravel that helps you analyze and interact with your customer base. It embeds powerful analytics functionality directly into your Laravel application, giving you complete control over your customer data while providing the tools you need to make data-driven decisions.

Unlike external analytics platforms, Stickle runs entirely within your Laravel application. You own your data, and you can query it using familiar Eloquent methods.

## When to Use Stickle

Stickle is ideal for Laravel applications that need to:

- **Build real-time customer analytics dashboards** - Get insights without external services
- **Trigger notifications based on user behavior** - Respond to what users do in your app
- **Segment customers for targeted engagement** - Identify high-value or at-risk customers automatically
- **Track customer health and engagement** - Monitor metrics that matter to your business
- **Understand product usage patterns** - See which features drive value

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

## What Stickle is Not

Stickle isn't a replacement for Google Analytics. It doesn't track anonymous visitors and isn't designed for marketing analytics. Stickle focuses on authenticated customers and helping you understand user behavior after they sign up.

## Next Steps

Ready to get started?

::: tip Basic Setup
Follow our [Installation Guide](/guide/installation) to get Stickle running in your Laravel application in 15 minutes.
:::

Or dive deeper:
- [Configuration](/guide/configuration) - Complete configuration reference
- [Tracking Attributes](/guide/tracking-attributes) - Learn how to track model attributes
- [Customer Segments](/guide/segments) - Build powerful customer segments
- [StickleUI Dashboard](/guide/stickle-ui) - Explore the built-in analytics dashboard
