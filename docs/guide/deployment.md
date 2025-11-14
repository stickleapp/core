---
outline: deep
---

# Deployment

Complete guide for deploying Stickle to production environments. Follow this checklist to ensure optimal performance, reliability, and security.

## Pre-Deployment Checklist

Before deploying Stickle to production, verify:

- [ ] Laravel 12.0+ and PHP 8.2+ installed
- [ ] Database migrations completed
- [ ] Queue workers configured
- [ ] Scheduled tasks configured
- [ ] Environment variables set
- [ ] Assets compiled
- [ ] Cache configured
- [ ] WebSocket service configured (if using real-time features)

## Environment Configuration

### Required Environment Variables

Set these in your `.env` file:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourapp.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue
QUEUE_CONNECTION=redis

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Stickle Configuration
STICKLE_TRACK_CLIENT_LOAD_MIDDLEWARE=true
STICKLE_TRACK_SERVER_LOAD_MIDDLEWARE=false
STICKLE_TRACK_SERVER_AUTHENTICATION_EVENTS=true
```

### Optional: Real-Time Features

If using StickleUI's real-time updates:

```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=https

# Or for Pusher
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=us2
```

## Database Setup

### Run Migrations

Apply all Stickle database migrations:

```bash
php artisan migrate --force
```

### Database Optimization

#### Add Indexes for Performance

Stickle automatically creates essential indexes, but for high-traffic applications, consider these additional indexes:

```php
// In a migration file
Schema::table('stickle_events', function (Blueprint $table) {
    $table->index(['object_uid', 'created_at']);
    $table->index(['session_uid', 'created_at']);
    $table->index(['name', 'created_at']);
});

Schema::table('stickle_attribute_changes', function (Blueprint $table) {
    $table->index(['object_uid', 'attribute_name', 'created_at']);
});

Schema::table('stickle_segment_members', function (Blueprint $table) {
    $table->index(['segment_id', 'joined_at']);
});
```

#### Table Partitioning (Optional)

For applications with millions of events, consider partitioning by date:

```sql
-- MySQL 8.0+ partitioning example
ALTER TABLE stickle_events
PARTITION BY RANGE (TO_DAYS(created_at)) (
    PARTITION p2024_01 VALUES LESS THAN (TO_DAYS('2024-02-01')),
    PARTITION p2024_02 VALUES LESS THAN (TO_DAYS('2024-03-01')),
    -- Add more partitions as needed
);
```

#### Regular Maintenance

Schedule regular database maintenance:

```bash
# Optimize tables monthly
php artisan db:optimize-tables

# Archive old events (if needed)
php artisan stickle:archive-events --older-than=90days
```

## Queue Workers

Stickle relies heavily on queues for background processing. Proper queue worker configuration is **critical** for production.

### Supervisor Configuration

Use Supervisor to keep queue workers running:

```ini
# /etc/supervisor/conf.d/stickle-worker.conf
[program:stickle-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

**Key Configuration Notes:**

- `numprocs=4` - Run 4 worker processes (adjust based on load)
- `--sleep=3` - Wait 3 seconds when queue is empty
- `--tries=3` - Retry failed jobs 3 times
- `--max-time=3600` - Restart worker after 1 hour to prevent memory leaks

### Start Supervisor

```bash
# Load the configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start stickle-worker:*

# Check status
sudo supervisorctl status stickle-worker:*
```

### Multiple Queue Priorities

For high-traffic applications, separate queues by priority:

```ini
# High-priority queue (segment calculations, attribute tracking)
[program:stickle-high]
command=php /path/to/your/app/artisan queue:work redis --queue=high --sleep=1 --tries=3
numprocs=2

# Default queue (event tracking)
[program:stickle-default]
command=php /path/to/your/app/artisan queue:work redis --queue=default --sleep=3 --tries=3
numprocs=4

# Low-priority queue (exports, reports)
[program:stickle-low]
command=php /path/to/your/app/artisan queue:work redis --queue=low --sleep=5 --tries=3
numprocs=1
```

### Queue Monitoring

Monitor queue health with Laravel Horizon (recommended):

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon:publish
```

Configure Horizon in `config/horizon.php`:

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['high', 'default', 'low'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 300,
        ],
    ],
],
```

Access Horizon dashboard at `/horizon` to monitor:
- Queue throughput
- Failed jobs
- Worker status
- Job metrics

## Scheduled Tasks

Stickle requires scheduled tasks for periodic operations.

### Configure Cron

Add this single cron entry:

```bash
* * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1
```

### Verify Schedule

Check what tasks will run:

```bash
php artisan schedule:list
```

You should see these Stickle tasks:

```
stickle:calculate-attributes    Every 5 minutes
stickle:export-segments         Every hour
stickle:cleanup-old-sessions    Daily at 2:00 AM
```

### Customize Schedule

Override default schedules in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Run attribute calculations every 10 minutes instead of 5
    $schedule->command('stickle:calculate-attributes')
        ->everyTenMinutes()
        ->withoutOverlapping();

    // Export segments more frequently for high-priority segments
    $schedule->command('stickle:export-segments --priority=high')
        ->everyFifteenMinutes()
        ->withoutOverlapping();

    // Regular segment exports
    $schedule->command('stickle:export-segments')
        ->hourly()
        ->withoutOverlapping();
}
```

## WebSockets (Real-Time Features)

If using StickleUI's real-time updates, configure Laravel Reverb or Pusher.

### Option 1: Laravel Reverb (Recommended)

**1. Install Reverb:**

```bash
php artisan reverb:install
```

**2. Configure Reverb:**

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https
```

**3. Run Reverb as a Service:**

```ini
# /etc/supervisor/conf.d/reverb.conf
[program:reverb]
command=php /path/to/your/app/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/reverb.log
```

**4. Proxy with Nginx:**

```nginx
server {
    listen 443 ssl;
    server_name yourapp.com;

    # WebSocket proxy
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
    }
}
```

**5. Restart Supervisor:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### Option 2: Pusher (Managed Service)

**1. Create Pusher Account:**

Sign up at https://pusher.com

**2. Configure Laravel:**

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=us2
```

**3. Install Pusher PHP SDK:**

```bash
composer require pusher/pusher-php-server
```

No additional server setup required - Pusher handles infrastructure.

## Application Optimization

### Cache Configuration

**1. Cache config and routes:**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**2. Optimize autoloader:**

```bash
composer install --optimize-autoloader --no-dev
```

**3. Enable OPcache:**

In `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
```

### Asset Compilation

Build production assets:

```bash
npm run build
```

If using StickleUI with custom styles:

```bash
# Build Stickle assets
php artisan stickle:build-assets
```

### Session Storage

Use Redis for session storage in production:

```env
SESSION_DRIVER=redis
SESSION_LIFETIME=120
```

## Security

### StickleUI Access Control

Restrict StickleUI access in `config/stickle.php`:

```php
'routes' => [
    'web' => [
        'prefix' => 'stickle',
        'middleware' => ['web', 'auth', 'can:view-stickle'],
    ],
],
```

Define the gate in `app/Providers/AuthServiceProvider.php`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('view-stickle', function ($user) {
    return in_array($user->email, [
        'admin@yourapp.com',
        'team@yourapp.com',
    ]);
});
```

### Rate Limiting

Add rate limiting to tracking endpoints in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        'throttle:1000,1', // 1000 requests per minute
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

### HTTPS Only

Force HTTPS in production (`app/Providers/AppServiceProvider.php`):

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

## Monitoring

### Application Monitoring

**1. Enable Failed Job Monitoring:**

```php
// In AppServiceProvider
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

Queue::failing(function (JobFailed $event) {
    // Log to monitoring service
    Log::error('Queue job failed', [
        'job' => $event->job->resolveName(),
        'exception' => $event->exception->getMessage(),
    ]);
});
```

**2. Track Performance:**

Monitor these key metrics:
- Queue job processing time
- Segment export duration
- Attribute calculation time
- Database query performance
- Memory usage

**3. Use Laravel Telescope (Development/Staging):**

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

::: warning
Do NOT use Telescope in production - it stores significant data and can impact performance.
:::

### Health Checks

Create a health check endpoint:

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'queue' => Queue::size() < 10000 ? 'ok' : 'warning',
        'cache' => Cache::has('health-check') ? 'ok' : 'error',
        'database' => DB::connection()->getPdo() ? 'ok' : 'error',
    ]);
});
```

### Log Management

**1. Configure log rotation:**

```php
// config/logging.php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'warning',
        'days' => 14,
    ],
],
```

**2. Separate Stickle logs:**

```php
'stickle' => [
    'driver' => 'daily',
    'path' => storage_path('logs/stickle.log'),
    'level' => 'info',
    'days' => 30,
],
```

**3. Monitor error rates:**

Use services like Sentry, Bugsnag, or Flare:

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your-dsn
```

## Deployment Workflow

### Zero-Downtime Deployment

**1. Use Laravel Envoy or Deployer:**

```php
// Envoy.blade.php
@servers(['production' => 'user@yourserver.com'])

@task('deploy', ['on' => 'production'])
    cd /path/to/your/app
    git pull origin main
    composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan queue:restart
    sudo supervisorctl restart all
@endtask
```

**2. Run deployment:**

```bash
envoy run deploy
```

### Post-Deployment Checks

After each deployment:

1. **Check queue workers:**
   ```bash
   sudo supervisorctl status
   ```

2. **Verify scheduled tasks:**
   ```bash
   php artisan schedule:list
   ```

3. **Test StickleUI:**
   Visit `/stickle` and verify dashboard loads

4. **Monitor logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Check for failed jobs:**
   ```bash
   php artisan queue:failed
   ```

## Performance Tuning

### High-Traffic Optimization

For applications with millions of events per day:

**1. Database Read Replicas:**

Configure read replicas in `config/database.php`:

```php
'mysql' => [
    'read' => [
        'host' => ['192.168.1.2', '192.168.1.3'],
    ],
    'write' => [
        'host' => ['192.168.1.1'],
    ],
    // ... other config
],
```

**2. Redis Clustering:**

Use Redis Cluster for distributed caching and queues.

**3. CDN for Assets:**

Serve StickleUI assets from a CDN:

```php
// config/stickle.php
'assets' => [
    'cdn_url' => 'https://cdn.yourapp.com/stickle',
],
```

**4. Horizontal Scaling:**

Scale queue workers across multiple servers with Redis queue.

**5. Event Sampling:**

For extremely high-traffic endpoints, sample events:

```php
// Only track 10% of page views
if (rand(1, 100) <= 10) {
    stickle.page();
}
```

## Backup Strategy

### Database Backups

**1. Automated daily backups:**

```bash
# In crontab
0 2 * * * mysqldump -u user -p password database > /backups/db-$(date +\%Y\%m\%d).sql
```

**2. Backup retention:**
- Daily backups: Keep 7 days
- Weekly backups: Keep 4 weeks
- Monthly backups: Keep 12 months

### Application Backups

Backup these directories:
- `storage/app` - Uploaded files
- `storage/logs` - Application logs
- `.env` - Environment configuration

## Scaling Considerations

### When to Scale

Consider scaling when:
- Queue jobs backing up (> 1000 pending jobs)
- Attribute calculations taking > 5 minutes
- Segment exports taking > 15 minutes
- Database CPU > 80% consistently
- Response times > 500ms

### Scaling Strategies

**1. Vertical Scaling:**
- Increase server resources (CPU, RAM)
- Optimize database (more RAM, faster disks)

**2. Horizontal Scaling:**
- Add more queue workers
- Database read replicas
- Load balancer for web servers

**3. Data Archival:**
- Archive events older than 90 days
- Move to cold storage for compliance

## Next Steps

- **[Troubleshooting](/guide/troubleshooting)** - Debug common production issues
- **[API Endpoints](/guide/api-endpoints)** - Integrate with external systems
- **[Configuration Reference](/guide/configuration)** - All configuration options
- **[Monitoring Guide](#monitoring)** - Set up comprehensive monitoring
