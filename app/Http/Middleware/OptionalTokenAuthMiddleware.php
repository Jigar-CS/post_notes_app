<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthMiddleware;
use Closure;
use Illuminate\Http\Request;

class OptionalTokenAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $auth = AuthMiddleware::authenticate($request);

        // Do not block requests; only attach auth payload when token is valid.
        $request->attributes->set('auth_user', $auth);

        return $next($request);
    }
}
