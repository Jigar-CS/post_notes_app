<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Laravel\Sanctum\PersonalAccessToken;

class AuthMiddleware {
    
    public static function getPrimaryUserId() {
        try {
            return UserModel::orderBy('user_id')->value('user_id');
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function isPrimaryUser($user) {
        if (!$user || !isset($user['user_id'])) {
            return false;
        }

        $primaryUserId = self::getPrimaryUserId();
        return $primaryUserId !== null && (int) $user['user_id'] === (int) $primaryUserId;
    }
    
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
                            'is_primary_user' => self::isPrimaryUser(['user_id' => $user->user_id])
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

    public static function ownsResource($user, $resourceOwnerId) {
        return $user && isset($user['user_id']) && $user['user_id'] == $resourceOwnerId;
    }

   
    public static function unauthorizedResponse($message = 'Unauthorized. Insufficient permissions.') {
        return response()->json(['status' => 403, 'error' => $message], 403);
    }
}
