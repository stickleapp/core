---
outline: deep
---

# Webhooks & External Integrations

Stickle does not include built-in webhook functionality. Instead, it provides a comprehensive real-time broadcasting system using Laravel's broadcasting capabilities with WebSockets. This document explains the current real-time capabilities and how to implement webhooks if needed.

## Current State: Broadcasting vs Webhooks

Stickle was designed with real-time browser-based updates in mind, using Laravel Broadcasting (Echo/Reverb) rather than traditional webhooks. This provides several advantages:

- **Real-time updates**: Immediate UI updates without polling
- **Bidirectional communication**: Two-way data flow between client and server
- **Built-in authentication**: Laravel's broadcasting authorization
- **Type safety**: Strongly typed event payloads

## Broadcasting System

### Available Events

Stickle broadcasts the following events in real-time:

| Event | Purpose | Channels |
|-------|---------|----------|
| `ModelEnteredSegment` | When a model joins a segment | firehose, object-specific |
| `ModelExitedSegment` | When a model leaves a segment | firehose, object-specific |
| `ModelAttributeChanged` | When tracked attributes change | firehose, object-specific |
| `Track` | Custom tracking events | firehose, object-specific |
| `Page` | Page view events | firehose, object-specific |
| `Identify` | User identification events | firehose, object-specific |
| `Group` | Group association events | firehose, object-specific |
| `RequestReceived` | HTTP request logging | firehose, object-specific |

### Channel Structure

**Firehose Channel**: `stickle.firehose`
- Receives all Stickle events
- Useful for system-wide monitoring
- Configurable via `STICKLE_BROADCASTING_CHANNEL_FIREHOSE`

**Object-Specific Channels**: `stickle.object.{model}.{id}`
- Receives events for specific model instances
- Format: `stickle.object.app-models-user.123`
- Configurable via `STICKLE_BROADCASTING_CHANNEL_OBJECT`

### Listening to Events

#### Frontend (Laravel Echo)

```javascript
// Listen to all events on firehose
Echo.channel('stickle.firehose')
    .listen('ModelEnteredSegment', (e) => {
        console.log('Model entered segment:', e);
    })
    .listen('ModelAttributeChanged', (e) => {
        console.log('Attribute changed:', e);
    });

// Listen to specific model events
Echo.channel('stickle.object.app-models-user.123')
    .listen('Track', (e) => {
        console.log('User tracked event:', e);
    });
```

#### Backend (Event Listeners)

```php
<?php

namespace App\Listeners;

use StickleApp\Core\Events\ModelEnteredSegment;

class HandleSegmentEntry
{
    public function handle(ModelEnteredSegment $event): void
    {
        $model = $event->model;
        $segment = $event->segment;
        
        // Handle the segment entry
        // e.g., send email, update external system, etc.
    }
}
```

Register in `EventServiceProvider`:

```php
protected $listen = [
    \StickleApp\Core\Events\ModelEnteredSegment::class => [
        \App\Listeners\HandleSegmentEntry::class,
    ],
    \StickleApp\Core\Events\ModelAttributeChanged::class => [
        \App\Listeners\HandleAttributeChange::class,
    ],
];
```

## Implementing Webhooks

If you need traditional webhook functionality, you can implement it using Stickle's events:

### 1. Create a Webhook Service

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function __construct(
        private string $webhookUrl,
        private ?string $secret = null
    ) {}

    public function send(string $event, array $payload): bool
    {
        $data = [
            'event' => $event,
            'payload' => $payload,
            'timestamp' => now()->toISOString(),
        ];

        if ($this->secret) {
            $data['signature'] = $this->generateSignature($data);
        }

        try {
            $response = Http::timeout(30)
                ->post($this->webhookUrl, $data);

            Log::info('Webhook sent', [
                'url' => $this->webhookUrl,
                'event' => $event,
                'status' => $response->status(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Webhook failed', [
                'url' => $this->webhookUrl,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function generateSignature(array $data): string
    {
        return hash_hmac('sha256', json_encode($data), $this->secret);
    }
}
```

### 2. Create Event Listeners

```php
<?php

namespace App\Listeners;

use App\Services\WebhookService;
use StickleApp\Core\Events\ModelEnteredSegment;

class SendSegmentWebhook
{
    public function __construct(private WebhookService $webhookService) {}

    public function handle(ModelEnteredSegment $event): void
    {
        $this->webhookService->send('model.entered_segment', [
            'model_type' => get_class($event->model),
            'model_id' => $event->model->getKey(),
            'segment_id' => $event->segment->id,
            'segment_name' => $event->segment->name,
            'model_data' => $event->model->toArray(),
        ]);
    }
}
```

### 3. Configure Service Container

```php
// In AppServiceProvider::register()
$this->app->singleton(WebhookService::class, function () {
    return new WebhookService(
        webhookUrl: config('services.webhook.url'),
        secret: config('services.webhook.secret')
    );
});
```

### 4. Add Configuration

```php
// config/services.php
'webhook' => [
    'url' => env('WEBHOOK_URL'),
    'secret' => env('WEBHOOK_SECRET'),
],
```

### 5. Queue Processing (Recommended)

For reliability, implement webhook delivery via queues:

```php
<?php

namespace App\Jobs;

use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWebhookJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $event,
        private array $payload
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $webhookService->send($this->event, $this->payload);
    }
}
```

Update your listener:

```php
public function handle(ModelEnteredSegment $event): void
{
    SendWebhookJob::dispatch('model.entered_segment', [
        'model_type' => get_class($event->model),
        'model_id' => $event->model->getKey(),
        'segment_id' => $event->segment->id,
        // ... other data
    ]);
}
```

## Third-Party Integration Patterns

### Using Spatie Webhooks Package

If you need advanced webhook functionality, consider using Spatie's webhook packages:

```bash
composer require spatie/laravel-webhook-server
```

```php
<?php

namespace App\Listeners;

use Spatie\WebhookServer\WebhookCall;
use StickleApp\Core\Events\ModelEnteredSegment;

class SendSpatieWebhook
{
    public function handle(ModelEnteredSegment $event): void
    {
        WebhookCall::create()
            ->url('https://example.com/webhooks/stickle')
            ->payload([
                'event' => 'model.entered_segment',
                'model_type' => get_class($event->model),
                'model_id' => $event->model->getKey(),
                'segment_id' => $event->segment->id,
            ])
            ->useSecret('webhook-secret')
            ->dispatch();
    }
}
```

### Integration with External Services

#### Zapier Integration

```php
public function handle(ModelEnteredSegment $event): void
{
    Http::post('https://hooks.zapier.com/hooks/catch/your-webhook-url', [
        'event_type' => 'segment_entered',
        'user_email' => $event->model->email,
        'segment_name' => $event->segment->name,
        'timestamp' => now()->toISOString(),
    ]);
}
```

#### Slack Notifications

```php
public function handle(ModelEnteredSegment $event): void
{
    Http::post(config('services.slack.webhook_url'), [
        'text' => sprintf(
            'User %s entered segment "%s"',
            $event->model->email,
            $event->segment->name
        ),
        'channel' => '#analytics',
    ]);
}
```

#### Custom CRM Integration

```php
public function handle(ModelAttributeChanged $event): void
{
    if ($event->attribute_name === 'subscription_status') {
        Http::withToken(config('services.crm.api_key'))
            ->put("https://api.crm.com/contacts/{$event->model->id}", [
                'subscription_status' => $event->new_value,
                'updated_at' => now()->toISOString(),
            ]);
    }
}
```

## Best Practices

### Error Handling and Retries

```php
class WebhookService
{
    public function sendWithRetry(string $event, array $payload, int $maxRetries = 3): bool
    {
        $attempt = 1;
        
        while ($attempt <= $maxRetries) {
            if ($this->send($event, $payload)) {
                return true;
            }
            
            $delay = min(60, (2 ** $attempt)); // Exponential backoff
            sleep($delay);
            $attempt++;
        }
        
        return false;
    }
}
```

### Webhook Verification

```php
// For receiving webhooks from external services
class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();
        
        if (!$this->verifySignature($signature, $payload)) {
            abort(401, 'Invalid signature');
        }
        
        $data = $request->json()->all();
        
        // Process webhook data
        ProcessIncomingWebhook::dispatch($data);
        
        return response()->json(['status' => 'received']);
    }
    
    private function verifySignature(string $signature, string $payload): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, config('webhook.secret'));
        return hash_equals($expectedSignature, $signature);
    }
}
```

### Rate Limiting

```php
class WebhookService
{
    private function shouldSend(string $event): bool
    {
        $key = "webhook:{$event}:" . now()->format('Y-m-d-H-i');
        $count = Cache::get($key, 0);
        
        if ($count >= 100) { // Max 100 per minute
            return false;
        }
        
        Cache::put($key, $count + 1, 60);
        return true;
    }
}
```

## Monitoring and Debugging

### Webhook Logs

```php
class WebhookService
{
    public function send(string $event, array $payload): bool
    {
        $startTime = microtime(true);
        
        try {
            $response = Http::timeout(30)->post($this->webhookUrl, $data);
            
            $duration = (microtime(true) - $startTime) * 1000;
            
            Log::channel('webhooks')->info('Webhook delivered', [
                'event' => $event,
                'url' => $this->webhookUrl,
                'status' => $response->status(),
                'duration_ms' => $duration,
                'payload_size' => strlen(json_encode($payload)),
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::channel('webhooks')->error('Webhook failed', [
                'event' => $event,
                'url' => $this->webhookUrl,
                'error' => $e->getMessage(),
                'duration_ms' => (microtime(true) - $startTime) * 1000,
            ]);
            
            return false;
        }
    }
}
```

### Health Checks

```php
class WebhookHealthCheck
{
    public function check(): bool
    {
        try {
            $response = Http::timeout(5)
                ->post($this->webhookUrl, ['ping' => true]);
                
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

## Conclusion

While Stickle doesn't include built-in webhooks, its event-driven architecture makes it easy to implement webhook functionality when needed. The broadcasting system provides real-time capabilities that often eliminate the need for traditional webhooks in browser-based applications.

For external integrations, implementing custom webhook listeners on Stickle events provides the flexibility to integrate with any third-party service while maintaining full control over delivery, retries, and error handling.
