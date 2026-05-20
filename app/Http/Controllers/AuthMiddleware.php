<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\UserModel;

class AuthMiddleware {
    // Returns authenticated user array on success, or null on failure
    public static function authenticate(Request $request) {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

        // Check Authorization header first (Bearer token)
        $header = $request->header('Authorization');
        if ($header && preg_match('/Bearer\s+(\S+)/', $header, $m)) {
            $token = $m[1];
            if (isset($_SESSION['token']) && $_SESSION['token'] === $token && isset($_SESSION['user'])) {
                return $_SESSION['user'];
            }
        }

        // Fallback to session-based auth
        if (isset($_SESSION['user']) && isset($_SESSION['token'])) {
            return $_SESSION['user'];
        }

        return null;
    }

    public static function requireAuth(Request $request) {
        $u = self::authenticate($request);
        if (!$u) {
            return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401);
        }
        return $u;
    }
}
