---
outline: deep
---

# What is Stickle?

Stickle is a customer analytics and engagement package for Laravel. Use it to:

-   Build real-time customer analytics dashboards;
-   Trigger Laravel notifications based on end-user behavior;
-   Highlight important customer behavior in your admin panel; or
-   Segment your customer base (ex. 'My at-risk customers').

Stickle provides the knowledge you need to make educated decisions about your customers _and_ to create powerful, real-time functionality in your application.

## What Stickle is not...

Stickle isn't a replacement for Google Analytics. It doesn't track unauthenticated users and isn't particularly interested in users before they become customers.

## StickleCore <Badge type="warning" text="MIT License" />

StickleCore is the foundation of Stickle that runs on your application server(s) gathering and preprocessing data about your users:

-   Tracks user attributes and behavior via a Javascript tracking code;
-   Logs authentication events;
-   Logs changes in model attributes;
-   Logs user-defined server-side events;
-   Exposes Eloquent methods for querying your customers;
-   Exposes a REST API endpoint for ingesting events from other channels (Mobile, etc);
-   Provides the ability to define customer segments 'as-code' and track these segments over time.

## StickleUI <Badge type="warning" text="MIT License" />

<Badge type="danger" text="StickleUI is under development and many of the features below are coming soon." />

StickleUI is bundled with StickleCore, providing analytics and reporting for StickleCore. StickleUI provides some out-of-the-box reports on your customers and allows you to build your own reports, customized to your business.

You can:

-   Browse and search users and gropus;
-   Browse your segments of users and groups;
-   Export segments to CSV for further processing;
-   View segment-level metrics;
-   View user- and group-level metrics;
-   View user-, group- and segment-level events in real-time.

StickleUI is composed of a rich library of Laravel Blade components that can be assembled to provide your team with information it needs.

StickleUI also has some low-level Blade Components which you can extend to make StickleUI fit your own use case.
