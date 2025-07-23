---
outline: deep
---

# Getting Started

## Installation

### Prerequisites

Stickle requires:

-   PHP 8.2+
-   Laravel 12.0+.

### Include

You may use Composer to require stickle into your PHP project:

```
$ composer require stickleapp/core
```

### Install

Stickle provides an installer that will guide you through your Stickle installation and configuration. You can run the intaller via the `artisan` command:

```
$ php artisan stickle:configure
```

### Advanced

The following are largely handled by the `stickle:configure` command but you may need to run them independently at some point.

#### Publish Files

You must publish files from Stickle to your project.

```
$ php artisan vendor:publish --provider="StickleApp\Core\CoreServiceProvider"
```

### Manual Configuration

Stickle ships with sensible defaults that work with most 'out-of-the-box' Laravel installations.

The `stickle:configure` artisan command will publish `/config/stickle.php` and setup your initial values.

You can manually configure these settings if necessary.

### Migrations

`stickle:configure` will run the required migrations but you can run them manually if necessary `php artisan migrate`.

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
