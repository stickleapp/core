---
outline: deep
---

# Getting Started

## Installation

### Prerequisites

Stickle requires:

-   PHP 8.3+
-   Laravel 11.0+.

### Include

You may use Composer to require stickle into your PHP project:

```
$ composer require stickleapp/core
```

### Install

Stickle provides an installer that will guide you through your Stickle installation and configuration. You can run the intaller via artisan:

```
$ php artisan stickle:install
```

### Run

Stickle provides a command that will start any processes required for running Stickle locally.

```
$ php artisan stickle:run
```

### Advanced

The following are largely handled by the `stickle:install` and `stickle:run` commands but you may need to run them independently at some point.

### Publish Files

You must publish files from Stickle to your project.

```
$ php artisan vendor:publish
```

### Configure Package

Stickle ships with sensible defaults that work with most 'out-of-the-box' Laravel installations.

However, we recommend you review these settings and adjust them as, necessary, to optimize your setting.

These can be found in `/config/stickle.php`.

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

You can confirm Stickle is installed correctly by visiting the `/stickle-demo` route in the web browser.
