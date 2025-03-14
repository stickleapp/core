# StickleApp Core

This document is to provide context to an AI agent before answering questions.

## Project Overview

StickleApp is a customer analytics and engagement package for Laravel that helps developers track user behavior and attributes, analyze customer data (individually and as 'segments' of customers), and increase engagement between customers and the application.

## Key Features

-   **User Behavior Tracking**: Tracks page views and custom events
-   **Customer Attribute Auditing**: Tracks model attributes over time
-   **Customer Segment Tracking**: Define segments in code and track segment statistics over time
-   **Event-Driven Architecture**: Trigger events and broadcast them to web users via websockets
-   **Customer Analytics**: Stickle provides a reporting UI for viewing user events and KPIs.

## Architecture

StickleApp Core follows a Laravel-centric architecture with:

-   **Models**: Represent data entities like segments, user attributes
-   **Events & Listeners**: Event-driven system for tracking users, identifying changes in user attributes and existence in segments
-   **Commands**: Background processes for analytics and data transformations
-   **Middleware**: HTTP middleware for tracking requests and injecting tracking code
-   **Eloquent Scopes**: Extend Eloquent queries with custom filtering logic

## Data Storage

-   Uses PostgreSQL with partitioned tables for analytics data
-   Leverages JSON columns for flexible attribute storage
-   Implements efficient time-series data storage patterns

## Key Components

-   **Tracking**: Client and server-side tracking via middleware and/or a javascript tracking snippet
-   **Segments**: Grouping users by defined criteria using both standard and custom Eloquent filters
-   **Attributes**: Storing and auditing changes to user/group properties
-   **Events**: Listening and responding to user actions
-   **Statistics**: Aggregating metrics for reporting and analysis

## Extension Points

Extension points are elements of a software system designed to allow developers to extend or customize the functionality without modifying the core code. They're intentional interfaces or mechanisms where the system is designed to be extended.

Extension points for StickleApp / Core include:

-   **Models**: Models (typically `User`) can be assigned the `StickleEntity` trait. This enables developers to use custom filters via a Eloquent scopes (`scopeStickle` and `orScopeStickle`)
-   **Custom segments**: Developers can create Segments which define a subset of `StickleEntity` models using standard Eloquent filters or Stickle filters appended to Eloquent builders using the added scopes
-   **Segment Event Listeners**: Developers can listen for when models enter and exit segments
-   **Custom attribute listeners**: Developers can listen for changes to specific attributes defined in the `observedAttributes` model.
-   **Custom event listeners**: Developers can define listeners to client-side events.

## Usage Examples

See documentation for detailed usage examples (/docs).

## Licensing

StickleCore is MIT licensed.
