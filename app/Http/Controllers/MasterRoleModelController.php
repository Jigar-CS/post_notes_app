<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\MasterRoleModel;
use App\Http\Controllers\AuthMiddleware;
class MasterRoleModelController extends Controller {
    public function createRole(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "role_name" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can create roles
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can create roles.'], 403); }
            // Only admins can create roles
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can create roles.'], 403); }
            $role = new MasterRoleModel();
            $role->role_name = $request->input('role_name');
            $role->role_status = 1;
            $role->created_by = $auth['user_id'];
            try {
                $result = $role->save();
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
    public function fetchAllRoles(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            'offset' => 'required|integer',
            'limit' => 'required|integer'
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $data = array();
            $data["offset"] = $request->input("offset");
            $data["limit"] = $request->input("limit");
            if ($request->has('search') && $request->input('search') != "") { $data['search'] = $request->input('search'); }
            try {
                $query = MasterRoleModel::query();
                if (isset($data['search'])) { $query->where('role_name', 'LIKE', '%' . $data['search'] . '%'); }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                $roles = $query->get();
                return response()->json(['status' => 200, 'count' => count($roles), 'data' => $roles], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchSingleRole(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "role_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $role = MasterRoleModel::where('role_id', $request->input('role_id'))->first();
                return response()->json(['status' => 200, 'data' => $role], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updateRole(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "role_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can update roles
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can update roles.'], 403); }
            // Only admins can update roles
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can update roles.'], 403); }
            $role = new MasterRoleModel();
            $newrequest = $request->except(['role_id']);
            $newrequest['updated_by'] = $auth['user_id'];
            try {
                $result = $role->where('role_id', $request->input('role_id'))->update($newrequest);
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
    public function deleteRole(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "role_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can delete roles
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can delete roles.'], 403); }
            // Only admins can delete roles
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can delete roles.'], 403); }
            $role = new MasterRoleModel();
            $request->request->add(['role_status' => 0, 'updated_by' => $auth['user_id']]);
            $newrequest = $request->except(['role_id']);
            try {
                $result = $role->where('role_id', $request->input('role_id'))->update($newrequest);
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
}