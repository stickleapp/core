<?php

namespace Dclaysmith\LaravelCascade\Middleware;

use Carbon\Carbon;
use Closure;
use Dclaysmith\LaravelCascade\Events\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * https://medium.com/@mehhfooz/log-requests-and-responses-in-laravel-f859d1f47b74
 */
class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $request->user()) {
            return $response;
        }

        Log::debug('Request received by middleware:', [
            $request,
        ]);

        $headers = $request->header();

        $dt = new Carbon;

        $data = [
            'model' => get_class($request->user()),
            'object_uid' => $request->user()?->id,
            'session_uid' => $request->session()->getId(),
            'url' => $request->fullUrl(),
            'path' => $request->getPathInfo(),
            'host' => $request->getHost(),
            'search' => $request->getQueryString(),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_content' => $request->query('utm_content'),
            'created_at' => $dt,
            'updated_at' => $dt,
        ];

        Page::dispatch($data);

        return $response;
    }
}
