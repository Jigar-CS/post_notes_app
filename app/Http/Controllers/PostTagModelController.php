<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\PostTagModel;
use App\Http\Controllers\AuthMiddleware;
class PostTagModelController extends Controller {
    public function attachTag(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "tag_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins and authors can attach tags to posts/notes
            if ($auth['role_id'] == 3) { return response()->json(['status' => 403, 'error' => 'Forbidden: Contributors cannot attach tags.'], 403); }
            $pt = new PostTagModel();
            $pt->post_id = $request->input('post_id');
            $pt->note_id = $request->input('note_id');
            $pt->tag_id = $request->input('tag_id');
            try {
                $result = $pt->save();
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
    public function detachTag(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "post_tag_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins and authors can detach tags
            if ($auth['role_id'] == 3) { return response()->json(['status' => 403, 'error' => 'Forbidden: Contributors cannot detach tags.'], 403); }
            try {
                $result = PostTagModel::where('post_tag_id', $request->input('post_tag_id'))->delete();
                if ($result) {
                    return response()->json(['status' => 200, 'data' => $result], 200);
                } else {
                    return response()->json(['status' => 400, 'error' => 'No rows deleted.'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
}