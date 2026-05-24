<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthMiddleware;
use Closure;
use Illuminate\Http\Request;

class TokenAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $auth = AuthMiddleware::authenticate($request);

        if (!$auth) {
            return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401);
        }

        // Attach authenticated user payload for optional controller use.
        $request->attributes->set('auth_user', $auth);

        return $next($request);
    }
}
