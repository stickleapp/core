# PRE-RELEASE SOFTWARE

## This software is not ready to be used. Contact me dclaysmith@gmail.com.

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

## Running Migrations

After you have specified your configuration options in the installation script, run migrations:

    php artisan migrate

## Publishing Assets

Stickle ships prebuilt CSS and JS. Publish them into your application's `public` directory:

    php artisan vendor:publish --tag=package-assets --force

Add this to your deploy script rather than running it once. Asset filenames are
content-hashed, so every Stickle release produces new ones and a stale publish
leaves the UI without styles. If the assets are missing, Stickle raises
`ViteManifestNotFoundException` naming the expected path.

You do not need Node or a build step in your application — the assets are built
and committed in the package.

### Grant access

Stickle is closed until you say who may open it. Add to `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewStickle', fn ($user) => $user->is_admin);
```

Every Stickle URL returns 403 until this is defined. This Gate does **not**
cover the broadcast channels; see "Known limitation: broadcasting is not
authenticated" below. Stickle does not scope data by tenant, so anyone this
Gate allows sees every tenant's data — in a multi-tenant application this
ability should name administrators, not customer-facing staff.

### Known limitation: broadcasting is not authenticated

Stickle's realtime broadcast stream is **not** authenticated. The events in
`src/Events/` broadcast on public channels (`new Channel`, not
`PrivateChannel`), and the JS client subscribes with `Echo.channel()`, not
`Echo.private()`. Laravel only runs channel authorizers — including the
`viewStickle` check in `routes/channels.php` — for `private-`/`presence-`
prefixed channels, so they are never consulted here.

Practically: anyone holding your application's Reverb/Pusher app key —
public by design, since it ships in your frontend bundle — can subscribe to
Stickle's channels directly and receive every tracked event in real time,
including the full model row on attribute-change events, without passing
the Gate above and without being authenticated at all.

Until the channels and client are converted to private/presence channels
(separate work, not done in this release), your realistic options are to
disable broadcasting for Stickle or to accept this exposure knowingly.

## Initialization

When complete you can run Stickle with the following command:

    php artisan run:stickle

This command will does the following:

-   activates cron tasks;
-   activates websockets server (if applicable).

You can access a test page at the URL specified in the terminal.

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
