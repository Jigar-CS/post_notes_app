<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\UserModel;
use App\Models\MasterCountryModel;
use App\Models\MasterRoleModel;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AuthMiddleware;
class UserModelController extends Controller {
    public function getRegistrationDropdowns(Request $request) {
        try {
            $countries = MasterCountryModel::where('country_status', 1)->get();
            $roles = MasterRoleModel::where('role_status', 1)->get();
            return response()->json(['status' => 200, 'data' => ['countries' => $countries, 'roles' => $roles]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
        }
    }
    public function registerUser(Request $request) {
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
            $user->role_id = $request->input('role_id');
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
        } else {
            try {
                $user = UserModel::where('email', $request->input('email'))->first();
                if ($user && Hash::check($request->input('password'), $user->password)) {
                    // Start session and generate API token for authentication
                    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
                    $token = \Illuminate\Support\Str::random(80);
                    // resolve role name
                    $roleName = null;
                    try {
                        $role = MasterRoleModel::where('role_id', $user->role_id)->first();
                        if ($role) { $roleName = $role->role_name; }
                    } catch (\Exception $e) {
                        // ignore
                    }
                    // store minimal user info in session
                    $_SESSION['user'] = [
                        'user_id' => $user->user_id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role_id' => $user->role_id,
                        'role_name' => $roleName
                    ];
                    $_SESSION['token'] = $token;
                    return response()->json(['status' => 200, 'data' => $user, 'token' => $token], 200);
                }
                return response()->json(['status' => 400, 'error' => 'Invalid credentials.'], 400);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchAllUsers(Request $request) {
        $valid = Validator::make($request->all(), []);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $auth = AuthMiddleware::authenticate($request);
            if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
            // Only admin can fetch all users
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: admin only.'], 403); }
            $userModel = new UserModel();
            $valid = Validator::make($request->all(), [
                'offset' => 'required|integer',
                'limit' => 'required|integer'
            ]);
            if ($valid->fails()) { return response()->json(['status' => 400, 'error' => $valid->errors()], 400); }
            $data = array();
            $data["offset"] = $request->input("offset");
            $data["limit"] = $request->input("limit");
            if ($request->has('search') && $request->input('search') != "") { $data['search'] = $request->input('search'); }
            
            try {
                $query = UserModel::query()
                    ->join('tbl_master_country', 'tbl_user.country_id', '=', 'tbl_master_country.country_id')
                    ->join('tbl_master_role', 'tbl_user.role_id', '=', 'tbl_master_role.role_id');
                
                if (isset($data['search'])) {
                    $query->where('tbl_user.username', 'LIKE', '%' . $data['search'] . '%');
                }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                
                $users = $query->select('tbl_user.*', 'tbl_master_country.country_name', 'tbl_master_role.role_name')->get();
                return response()->json(['status' => 200, 'count' => count($users), 'data' => $users], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchSingleUser(Request $request) {
        $valid = Validator::make($request->all(), [
            "user_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $auth = AuthMiddleware::authenticate($request);
                if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
                $user = UserModel::where('user_id', $request->input('user_id'))->first();
                if ($auth['role_id'] != 1 && $auth['user_id'] != $request->input('user_id')) {
                    return response()->json(['status' => 403, 'error' => 'Forbidden: cannot view other users.'], 403);
                }
                return response()->json(['status' => 200, 'data' => $user], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updateUser(Request $request) {
        $valid = Validator::make($request->all(), [
            "user_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $auth = AuthMiddleware::authenticate($request);
            if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
            if ($auth['role_id'] != 1 && $auth['user_id'] != $request->input('user_id')) {
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
        $valid = Validator::make($request->all(), [
            "user_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $auth = AuthMiddleware::authenticate($request);
            if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
            // Only admin can delete users
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: admin only.'], 403); }
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
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        // Unset session and destroy
        if (isset($_SESSION['user'])) { unset($_SESSION['user']); }
        if (isset($_SESSION['token'])) { unset($_SESSION['token']); }
        session_destroy();
        return response()->json(['status' => 200, 'data' => 'Logged out successfully.'], 200);
    }
}