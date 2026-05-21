<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\TagModel;
use App\Http\Controllers\AuthMiddleware;
class TagModelController extends Controller {
    public function createTag(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "tag_name" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can create tags
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can create tags.'], 403); }
            
            $tag = new TagModel();
            $tag->tag_name = $request->input('tag_name');
            $tag->tag_status = 1;
            try {
                $result = $tag->save();
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
    public function fetchAllTags(Request $request) {
        // Check authorization FIRST (before any input validation)
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { 
            return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); 
        }

        // Then validate input parameters
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
                $query = TagModel::query();
                if (isset($data['search'])) { $query->where('tag_name', 'LIKE', '%' . $data['search'] . '%'); }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                $tags = $query->get();
                return response()->json(['status' => 200, 'count' => count($tags), 'data' => $tags], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchSingleTag(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "tag_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $tag = TagModel::where('tag_id', $request->input('tag_id'))->first();
                return response()->json(['status' => 200, 'data' => $tag], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updateTag(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "tag_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can update tags
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can update tags.'], 403); }
            $tag = new TagModel();
            $newrequest = $request->except(['tag_id']);
            try {
                $result = $tag->where('tag_id', $request->input('tag_id'))->update($newrequest);
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
    public function deleteTag(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "tag_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can delete tags
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can delete tags.'], 403); }
            $tag = new TagModel();
            $request->request->add(['tag_status' => 0]);
            $newrequest = $request->except(['tag_id']);
            try {
                $result = $tag->where('tag_id', $request->input('tag_id'))->update($newrequest);
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