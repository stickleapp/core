<?php

declare(strict_types=1);

namespace StickleApp\Core\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use StickleApp\Core\Events\Page;

class RequestLogger
{
    /**
     * Routes/patterns to ignore
     *
     * @var array<string>
     */
    protected array $ignoredPatterns = [
        // Livewire
        'livewire/*',
        '*/livewire/*',

        // Telescope
        'telescope/*',
        'vendor/telescope/*',

        // Horizon
        'horizon/*',
        'vendor/horizon/*',

        // Health checks
        'health',
        'ping',
    ];

    /**
     * Handle an incoming request.
     *
     * Just pass through - tracking happens after response is sent
     */
    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request);
    }

    /**
     * Perform tracking after response has been sent to browser.
     *
     * This method is automatically called by Laravel after the response
     * has been sent to the client. This prevents tracking from slowing
     * down the user's request.
     */
    public function terminate(Request $request, mixed $response): void
    {
        if ($this->shouldIgnore($request)) {
            return;
        }

        $user = $request->user();
        $data = [
            'user' => $user,
            'model_class' => $user ? class_basename($user) : null,
            'object_uid' => $user ? (string) $user->id : null,
            'session_uid' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'url' => $request->fullUrl(),
            'path' => $request->getPathInfo(),
            'host' => $request->getHost(),
            'search' => $request->getQueryString(),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_content' => $request->query('utm_content'),
            'created_at' => new Carbon,
            'updated_at' => new Carbon,
        ];

        Page::dispatch($data);
    }

    /**
     * Determine if the request should be ignored
     */
    protected function shouldIgnore(Request $request): bool
    {
        if (! $request->user()) {
            return true; // Ignore requests without a user
        }

        // Check if it's a Livewire request
        if ($this->isLivewireRequest($request)) {
            return true;
        }

        // Check URL patterns
        foreach ($this->ignoredPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        // Check for Telescope header
        if ($request->hasHeader('X-Telescope-Request')) {
            return true;
        }

        return false;
    }

    /**
     * Check if this is a Livewire request
     */
    protected function isLivewireRequest(Request $request): bool
    {
        return $request->hasHeader('X-Livewire') ||
               $request->routeIs('livewire.*') ||
               str_contains($request->path(), 'livewire/message');
    }

    /**
     * Get the response status code safely
     */
    protected function getStatusCode(mixed $response): int
    {
        if (is_object($response) && method_exists($response, 'getStatusCode')) {
            return $response->getStatusCode();
        }

        if (is_object($response) && method_exists($response, 'status')) {
            return $response->status();
        }

        return 200;
    }

    /**
     * Calculate response time in milliseconds
     */
    protected function getResponseTime(): float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }

        return 0.0;
    }
}
