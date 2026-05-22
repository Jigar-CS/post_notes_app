<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\UserModel;
use App\Models\MasterCountryModel;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AuthMiddleware;
class UserModelController extends Controller {
    public function getRegistrationDropdowns(Request $request) {
        try {
            $countries = MasterCountryModel::where('country_status', 1)->get();
            return response()->json(['status' => 200, 'data' => ['countries' => $countries]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
        }
    }
    public function registerUser(Request $request) {
         $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json([
                'status' => 401, 
                'error' => 'Authorization required.'
            ], 401); }

        $valid = Validator::make($request->all(), [
            "username" => "required",
            "email" => "required|email",
            "password" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $user = new UserModel();
            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->country_id = $request->input('country_id');
            $user->role_id = 2;
            $user->user_status = 1;
            try {
                $result = $user->save();
                if ($result) {
                    return response()->json(['status' => 200, 'data' => $result], 200);
                } else {
                    return response()->json(['status' => 400, 'error' => 'Save returned false.'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function loginUser(Request $request) {
        $valid = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        }

        try {
            $adminAuth = AuthMiddleware::authenticate($request); // may be null if no token sent
            $user = UserModel::where('email', $request->input('email'))->first();

            // If a token was sent, ensure it belongs to primary user
            if ($adminAuth) {
                if (empty($adminAuth['is_primary_user'])) {
                    return response()->json(['status' => 401, 'error' => 'Authorization required: primary user token.'], 401);
                }
            } else {
                // No token provided: only allow login if the credentials belong to the primary user
                if (!$user || !AuthMiddleware::isPrimaryUser(['user_id' => $user->user_id])) {
                    return response()->json(['status' => 401, 'error' => 'Authorization required: primary user token or primary user credentials.'], 401);
                }
            }

            if ($user && Hash::check($request->input('password'), $user->password)) {
                // Generate Sanctum API token for authentication
                $token = $user->createToken('api_token')->plainTextToken;

                // return sanitized user (no password) and token
                $safe = [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'is_primary_user' => AuthMiddleware::isPrimaryUser(['user_id' => $user->user_id])
                ];
                return response()->json(['status' => 200, 'data' => $safe, 'token' => $token], 200);
            }
            return response()->json(['status' => 400, 'error' => 'Invalid credentials.'], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
        }
    }
    public function fetchAllUsers(Request $request) {
        // Check authorization FIRST (before any input validation)
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { 
            return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); 
        }
        // Only the first existing user can fetch all users
        if (empty($auth['is_primary_user'])) { 
            return response()->json(['status' => 403, 'error' => 'Forbidden: primary user only.'], 403); 
        }

        // Then validate input parameters
        $valid = Validator::make($request->all(), [
            'offset' => 'required|integer',
            'limit' => 'required|integer'
        ]);
        if ($valid->fails()) { 
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400); 
        }

        $data = array();
        $data["offset"] = $request->input("offset");
        $data["limit"] = $request->input("limit");
        if ($request->has('search') && $request->input('search') != "") { $data['search'] = $request->input('search'); }
        
        try {
            $query = UserModel::query()->withCountry();
            
            if (isset($data['search'])) {
                $query->where('tbl_user.username', 'LIKE', '%' . $data['search'] . '%');
            }
            if (isset($data['offset'])) { $query->skip($data['offset']); }
            if (isset($data['limit'])) { $query->take($data['limit']); }
            
            $users = $query->select('tbl_user.user_id', 'tbl_user.username', 'tbl_user.email', 'tbl_user.country_id', 'tbl_user.role_id', 'tbl_user.user_status', 'tbl_master_country.country_name')->get();
            return response()->json(['status' => 200, 'count' => count($users), 'data' => $users], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
        }
    }
    public function fetchSingleUser(Request $request) {
        // Check authorization FIRST (before any input validation)
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { 
            return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); 
        }

        // Then validate input parameters
        $valid = Validator::make($request->all(), [
            "user_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $user = UserModel::where('user_id', $request->input('user_id'))
                    ->select('user_id', 'username', 'email', 'country_id', 'role_id', 'user_status')
                    ->first();
                if (empty($auth['is_primary_user']) && $auth['user_id'] != $request->input('user_id')) {
                    return response()->json(['status' => 403, 'error' => 'Forbidden: cannot view other users.'], 403);
                }
                return response()->json(['status' => 200, 'data' => $user], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updateUser(Request $request) {
        // Check authorization FIRST (before any input validation)
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { 
            return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); 
        }

        // Then validate input parameters
        $valid = Validator::make($request->all(), [
            "user_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            if (empty($auth['is_primary_user']) && $auth['user_id'] != $request->input('user_id')) {
                return response()->json(['status' => 403, 'error' => 'Forbidden: cannot update other users.'], 403);
            }
            $user = new UserModel();
            $newrequest = $request->except(['user_id']);
            try {
                $result = $user->where('user_id', $request->input('user_id'))->update($newrequest);
                if ($result) {
                    return response()->json(['status' => 200, 'data' => $result], 200);
                } else {
                    return response()->json(['status' => 400, 'error' => "No rows updated or something went wrong."], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function deleteUser(Request $request) {
        // Check authorization FIRST (before any input validation)
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { 
            return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); 
        }

        // Then validate input parameters
        $valid = Validator::make($request->all(), [
            "user_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only the first existing user can delete users
            if (empty($auth['is_primary_user'])) { 
                return response()->json(['status' => 403, 'error' => 'Forbidden: primary user only.'], 403); 
            }
            $user = new UserModel();
            $request->request->add(['user_status' => 0]);
            $newrequest = $request->except(['user_id']);
            try {
                $result = $user->where('user_id', $request->input('user_id'))->update($newrequest);
                if ($result) {
                    return response()->json(['status' => 200, 'data' => $request->all()], 200);
                } else {
                    return response()->json(['status' => 400, 'error' => 'No rows updated or something went wrong.'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }

    public function logoutUser(Request $request) {
        // User must be authenticated to logout
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { 
            return response()->json(['status' => 401, 'error' => 'Authorization required. You must be logged in to logout.'], 401); 
        }

        // Revoke all Sanctum tokens for this user
        try {
            $user = UserModel::where('user_id', $auth['user_id'])->first();
            if ($user) {
                $user->tokens()->delete();
            }
        } catch (\Exception $e) { 
            // Continue with logout even if DB error
        }

        return response()->json(['status' => 200, 'data' => 'Logged out successfully.'], 200);
    }
}