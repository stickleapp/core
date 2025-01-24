<?php

namespace StickleApp\Core\Middleware;

use Carbon\Carbon;
use Closure;
use StickleApp\\Core\Core\Events\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $data = [
            'model' => get_class($request->user()),
            'object_uid' => $request->user()->id,
            'session_uid' => $request->session()->getId(),
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

        return $response;
    }
}
