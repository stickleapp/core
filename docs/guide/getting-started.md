---
outline: deep
---

# Getting Started

## Installation

### Prerequisites

Stickle requires:

-   PHP 8.3+
-   Laravel 11.0+.

### Installation

You may use Composer to require Cascade into your PHP project:

```
$ composer require stickleapp/core
```

### Configuration

Stickle ships with a command line installation wizard that will help you configure your project.

```
$ php artisan cascade:install
```

The installer will guide you through the setup process helping you set configuration options for your project. You can specify:

-   If you want to install the Cascade JS SDK and track client events;
-   If you want to track events raised by `Illuminate\Auth` events;
-   If you want to track each authenticated request via middleware; and
-   How you define the relationships between Users and Groups in your application.

### Migrations

Once you have installed and configured Stickle, you must run the required database migrations:

```
$ php artisan migrate
```

### Scheduled Tasks

Stickle runs several scheduled tasks in the background. Make sure you have a queue worker running. For development, run:

```
php artisan schedule:work

```

### Demo Page

You can confirm Stickle is installed correctly by visiting the `/stickle` route in the web browser.
