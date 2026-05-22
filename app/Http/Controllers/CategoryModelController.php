<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\CategoryModel;
use Illuminate\Support\Str;
use App\Http\Controllers\AuthMiddleware;
class CategoryModelController extends Controller {
    public function createCategory(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json([
                'status' => 401, 
                'error' => 'Authorization required.'
            ], 401); }
            
        $valid = Validator::make($request->all(), [
            "category_name" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only the first existing user can create categories
            if (empty($auth['is_primary_user'])) { return response()->json(['status' => 403, 'error' => 'Forbidden: primary user only.'], 403); }
            
            $category = new CategoryModel();
            $category->category_name = $request->input('category_name');
            $category->category_slug = Str::slug($request->input('category_name'));
            $category->category_status = 1;
            try {
                $result = $category->save();
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
    public function fetchAllCategories(Request $request) {
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
                $query = CategoryModel::query();
                if (isset($data['search'])) { $query->where('category_name', 'LIKE', '%' . $data['search'] . '%'); }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                $categories = $query->get();
                return response()->json(['status' => 200, 'count' => count($categories), 'data' => $categories], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchSingleCategory(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "category_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $category = CategoryModel::where('category_id', $request->input('category_id'))->first();
                return response()->json(['status' => 200, 'data' => $category], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updateCategory(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "category_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only the first existing user can update categories
            if (empty($auth['is_primary_user'])) { return response()->json(['status' => 403, 'error' => 'Forbidden: primary user only.'], 403); }
            $category = new CategoryModel();
            $newrequest = $request->except(['category_id']);
            if($request->has('category_name')) {
                $newrequest['category_slug'] = Str::slug($request->input('category_name'));
            }
            try {
                $result = $category->where('category_id', $request->input('category_id'))->update($newrequest);
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
    public function deleteCategory(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "category_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only the first existing user can delete categories
            if (empty($auth['is_primary_user'])) { return response()->json(['status' => 403, 'error' => 'Forbidden: primary user only.'], 403); }
            $category = new CategoryModel();
            $request->request->add(['category_status' => 0]);
            $newrequest = $request->except(['category_id']);
            try {
                $result = $category->where('category_id', $request->input('category_id'))->update($newrequest);
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