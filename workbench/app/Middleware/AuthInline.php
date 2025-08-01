<?php

namespace Workbench\App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthInline
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $credentials = [
            'email' => $request->header('email'),
            'password' => $request->header('password'),
        ];

        Log::info('Authenticating with credentials', $credentials);

        if (! Auth::attempt($credentials)) {
            return response()->json($credentials, 401);
        }

        return $next($request);
    }
}
