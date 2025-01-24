<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final readonly class InjectJavascriptTrackingCode
{
    /**
     * Creates a new middleware instance.
     */
    public function __construct() {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->headers->get('Content-Type') === 'text/html; charset=UTF-8') {
            $content = (string) $response->getContent();

            if (! str_contains($content, '</html>') || ! str_contains($content, '</body>')) {
                return $response;
            }

            $this->inject($response);
        }

        return $response;
    }

    /**
     * Inject the JavaScript library into the response.
     */
    private function inject(Response $response): void
    {
        $routePrefix = '';

        $response->setContent(
            str_replace(
                '</body>',
                sprintf(<<<'HTML'
                            <script>
                                %s
                            </script>
                        </body>
                        HTML,
                    str_replace(
                        ['%_CSRF_TOKEN_%', '%_ROUTE_PREFIX_%'],
                        [(string) csrf_token(), $routePrefix],
                        File::get(__DIR__.'/../../resources/js/tracking/src/tracking.js')
                    ),
                ),
                (string) $response->getContent(),
            )
        );
    }
}
