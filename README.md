Cascade is a package for Laravel that helps you analyze and interact with your customer base.

# Use Cases

Cascade allows you to embed customer analytics and engagement functionality in your Laravel application. Use it to:

-   Build real-time customer analytics dashboard;
-   Trigger Laravel notifications based on end-user behavior;
-   Highlight important customer behavior in your admin panel;
-   Segment your customer base to identify high value or at-risk customers in realtime.

Cascade Apps allow you to do even more.

# Cascade Core

Cascade Core is the foundation of Cascade that:

-   Tracks user attributes and behavior via a Javascript tracking code;
-   Logs authentication events;
-   Logs changes in model attributes;
-   Logs user-defined server-side events;
-   Exposes Eloquent methods for querying your customers;
-   Exposes a REST API for ingesting events from other channels (Mobile, etc);
-   Provides the ability to define customer segments 'as-code' and track these segments over time.

# Cascade Apps

Cascade Apps extend the funtionality of Cascade Core. Anyone can write a Cascade app but we'll provide some first-party apps to get people started.

## Cascade Webhooks

Cascade Webhooks allow you to send Cascade events to a defined Webhook endpoint.

## Cascade Websockets

Built on Laravel Reverb, Cascade Websockets allow you to send notifications to connected clients via Websockets.

## Cascade Health

Build customer health scores based on Cascade Core.

## Cascade Orchestrations

Create workflows triggered on data in Cascade Core.

## Cascade Dashboards

Create shareable dashboards containing metrics gleaned from your Customer data.

## Cascade Updates

Share customer-specific email updates to your customers to demonstrate the value they provide.

# Cascade Pro

Subscribe to Cascade Professional and get the following:

## Premium Cascade Screencasts

In addition to the free Cascade screencasts we regularly produce, we will be offering special Premium Screencasts for Cascade Pro subscribers.

## Cascade UI Professional

Cascade UI provides you with a turnkey customer analytics and engagement application including advanced UI options for

-   Configuring customer health scores;
-   Orchestrating and observing workflows;

# Getting Started

## Requirements

Cascade requires:

-   PHP 8.3+
-   Laravel 11.0+.

## Installation

You may use Composer to require Cascade into your PHP project:

    composer require dclaysmith/laravel-cascade

You may install Cascade into your Laravel project using the following command:

    php artisan install:cascade

The installer will guide you through the setup process helping you set configuration options for your project. You can specify:

-   If you want to install the Cascade JS SDK and track client events;
-   If you want to track events raised by Illuminate\Auth events;
-   If you want to track each authenticated event via middleware; and
-   How you define the relationships between `Users` and `Groups` in your application.

It will also prompt you to install desired first-party Cascade apps:

-   Cascade Webhooks
-   Cascade Health
-   Cascade Orchestrations
-   Cascade Dashboards

## Running Migrations

After you have specified your configuration options in the installation script, run migrations:

    php artisan migrate

## Initialization

When complete you can run Cascade with the following command:

    php artisan run:cascade

This command will does the following:

-   activates cron tasks;
-   activates websockets server (if applicable).

You can access a test page at the URL specified in the terminal.

# Advanced Configuration

Cascade will work out of the box using the configuration options specified during the installation process. You can override these (and several other) options in the `\config\cascade.php` file.

## Database Options

You can specify the following configuration options:

-   `connection`. (Defaults to `DB_CONNECTION` env value) This connection should contain the tracked models.
-   `tablePrefix`. (Default `lc*`) This will be prepended to the tables created by the migrations.

## Sync Schedule

Cascade runs several jobs to transform your data. You can update the frequency that these jobs run:

-   `ExportSegments`. (Default Every 360 minutes).
-   `RecordSegmentStatistics`. (Default Every 360 minutes).
-   `RecordEntityStatistics`. (Default Every 360 minutes).
-   `RollupEvents`. (Default Every 360 minutes).
-   `RollupPageViews`. (Default Every 360 minutes).
-   `RollupSessions`. (Default Every 360 minutes).

## Customer Models

-   `User`. By default, Cascade assumes the `App\Models\User` class is the user responsible for authentication events. You can override this class.
-   `Group`. You can **optionally** specify a Group class that represents a real world company, account, organization, etc. that a `User` belongs to.
-   `Relationship`. You should specify the relationship between the `Group` and the `User`. By default, the relationship is `Illuminate\Database\Eloquent\Relations\HasMany` meaning each `Group` has zero or more `Users` objects. Other options include:
    -   `Illuminate\Database\Eloquent\Relations\HasOne`. Each `User` belongs to one `Group` and each `Group` has exactly one `User`.
    -   `Illuminate\Database\Eloquent\Relations\BelongsToMany`. Each `User` can belong to zero or more `Groups` and each `Group` can have have zero or more `Users`.

## Tracking Options

Cascade can track requests and events on the server and on the client. There are several configuration options that determine the behavior of each method:

### Server

-   `loadMiddleware`. Default `true`. When `true`, Cascade loads middleware during the Package Service Provider's `boot` method.
-   `trackAuthenticationEvents`. Default `true`. Automatically log all authenticated `Illuminate\Auth` events.
-   `trackRequests`. Default `true`. Automatically track all authenticated requests made in the application.

### Client

-   `loadMiddleware`. Default `true`. When `true`, Cascade injects a tracking code via middleware during the Package Service Provider's `boot` method.
-   `trackPageViews`. Default `true`. When `true`, the Javascript tracking code will listen for pushState changes and record a pageview for each.
-   `routePrefix`. Default `''`. Tracking events are sent to the `/request` endpoint by default. If you move this endpoint to a different location, update `routePrefix` to reflect this location.

# How To

## Tracking End User Behavior

### On the Server

### On the Browser

## Tracking Model Attribute Changes

## Tracking User-Defined server events

ServerEventReceived::dispatch($data);

## Eloquent Models

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
