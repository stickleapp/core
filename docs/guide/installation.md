---
outline: deep
---

# Installation

## Prerequisites

Stickle requires:

-   PHP 8.2+
-   Laravel 12.0+.

## Installing Stickle

### Step 1: Require via Composer

Use Composer to require Stickle into your Laravel project:

```bash
composer require stickleapp/core
```

### Step 2: Run the Installer

Stickle provides an installer that will guide you through configuration. Run it via artisan:

```bash
php artisan stickle:install
```

The installer will:
- Publish configuration files
- Run database migrations
- Set up default tracking options

### Step 3: Run Migrations

Migrations are typically run by the installer, but you can run them manually if needed:

```bash
php artisan migrate
```

## Advanced Installation

The following are handled by the `stickle:install` command but you may need to run them independently at some point.

#### Publish Files

You must manually publish files from Stickle to your project.

```
$ php artisan vendor:publish --provider="StickleApp\Core\CoreServiceProvider"
```

#### Configuration

Stickle ships with sensible defaults that work with most 'out-of-the-box' Laravel installations.

The `stickle:install` artisan command will publish `/config/stickle.php` and setup your initial values.

You can manually configure these settings if necessary.

#### Migrations

The `stickle:install` command will run the required migrations automatically. If you need to run them manually:

```bash
php artisan migrate
```

## Running Stickle

Once installed, Stickle requires two background services to be running.

### Scheduled Tasks

Stickle runs several scheduled tasks in the background to process analytics data. During development, run:

```bash
php artisan schedule:work
```

For production, configure your server's cron to run Laravel's scheduler. Add this to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Laravel Reverb (Optional)

If you want real-time features (live event streaming, real-time UI updates), start Laravel Reverb. For development:

```bash
php artisan reverb:start
```

For production, run Reverb as a background service. See the [Laravel Reverb documentation](https://laravel.com/docs/reverb) for details.

## Next Steps

You're now ready to start using Stickle! Here's what to do next:

- **[Quick Start Guide](/guide/quick-start)** - Get up and running in 15 minutes
- **[Configuration](/guide/configuration)** - Fine-tune your Stickle installation
- **[Tracking Attributes](/guide/tracking-attributes)** - Learn how to track model attributes

For deployment to production, see our **[Deployment Guide](/guide/deployment)**.
