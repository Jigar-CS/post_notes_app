<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\PostTagModel;
class PostTagModelController extends Controller {
    public function attachTag(Request $request) {
        $auth = $request->attributes->get('auth_user');
        $valid = Validator::make($request->all(), [
            "tag_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only the first existing user can manage shared tag associations
            if (empty($auth['is_primary_user'])) { return response()->json(['status' => 403, 'error' => 'Forbidden: primary user only.'], 403); }
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
        $auth = $request->attributes->get('auth_user');
        $valid = Validator::make($request->all(), [
            "post_tag_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only the first existing user can manage shared tag associations
            if (empty($auth['is_primary_user'])) { return response()->json(['status' => 403, 'error' => 'Forbidden: primary user only.'], 403); }
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