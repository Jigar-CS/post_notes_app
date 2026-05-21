<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Laravel\Sanctum\PersonalAccessToken;

class AuthMiddleware {
    
   public static function authenticate(Request $request) {
        // Check Authorization header with Bearer token (Sanctum)
        $header = $request->header('Authorization');
        if ($header && preg_match('/Bearer\s+(\S+)/', $header, $m)) {
            $token_string = $m[1];
            
            // Resolve token using Sanctum
            try {
                $token = PersonalAccessToken::findToken($token_string);
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                    if ($user->user_status == 1) { // Only active users
                        return [
                            'user_id' => $user->user_id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'role_id' => $user->role_id
                        ];
                    }
                }
            } catch (\Exception $e) {
                // ignore DB errors and fall through
            }
        }

        // For API routes, require Bearer token explicitly - no fallback to session
        return null;
    }

  
    public static function requireAuth(Request $request) {
        $user = self::authenticate($request);
        if (!$user) {
            return response()->json(['status' => 401, 'error' => 'Authorization required. Please login to access this resource.'], 401);
        }
        return $user;
    }

    /**
     * Require specific role - returns error response if user doesn't have the required role
     * 
     * @param Request $request
     * @param array $allowedRoles - Array of role IDs that are allowed (e.g., [1] for admin only)
     * @return array|null - Returns user array if authorized, null otherwise (caller should return 403)
     */
    public static function requireRole(Request $request, array $allowedRoles) {
        $user = self::authenticate($request);
        if (!$user) {
            return null; // Not authenticated
        }
        if (!in_array($user['role_id'], $allowedRoles)) {
            return null; // Authenticated but insufficient role
        }
        return $user;
    }

   
    public static function isAdmin($user) {
        return $user && isset($user['role_id']) && $user['role_id'] == 1;
    }

    
    public static function isAuthor($user) {
        return $user && isset($user['role_id']) && ($user['role_id'] == 1 || $user['role_id'] == 2);
    }

   
    public static function isContributor($user) {
        return $user && isset($user['role_id']) && $user['role_id'] == 3;
    }

   
    public static function ownsResource($user, $resourceOwnerId) {
        return $user && isset($user['user_id']) && $user['user_id'] == $resourceOwnerId;
    }

   
    public static function unauthorizedResponse($message = 'Unauthorized. Insufficient permissions.') {
        return response()->json(['status' => 403, 'error' => $message], 403);
    }
}
