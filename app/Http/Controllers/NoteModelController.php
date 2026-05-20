<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\NoteModel;
use App\Models\PostTagModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Http\Controllers\AuthMiddleware;
class NoteModelController extends Controller {
    public function createNote(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        
        // Validation: accept category_name and tag names only
        $valid = Validator::make($request->all(), [
            "title" => "required",
            "content" => "required",
            "category_name" => "required|string",
            "user_id" => "nullable|exists:tbl_user,user_id",
            "tags" => "nullable|array",
            "tags.*" => "nullable|string"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        }
        
        try {
            // Get or create category by name (with slug)
            $categoryName = $request->input('category_name');
            $categorySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $categoryName), '-'));
            $category = CategoryModel::firstOrCreate(
                ['category_name' => $categoryName],
                ['category_slug' => $categorySlug, 'category_status' => 1]
            );
            $categoryId = $category->category_id;
            
            // Get or create tags by name
            $tagIds = [];
            if ($request->has('tags') && is_array($request->input('tags'))) {
                foreach ($request->input('tags') as $tagName) {
                    if (!empty($tagName)) {
                        $tag = TagModel::firstOrCreate(
                            ['tag_name' => $tagName],
                            ['tag_status' => 1]
                        );
                        $tagIds[] = $tag->tag_id;
                    }
                }
            }
            
            $note = new NoteModel();
            // Determine user for the note: admin may set `user_id`, others use their own id
            $userId = $auth['user_id'];
            if ($auth['role_id'] == 1 && $request->has('user_id')) {
                $userId = $request->input('user_id');
            }
            $note->user_id = $userId;
            $note->category_id = $categoryId;
            $note->title = $request->input('title');
            $note->content = $request->input('content');
            $note->note_status = 1;
            // audit
            $note->created_by = $auth['user_id'];
            
            $result = $note->save();
            if ($result) {
                // Attach resolved tags
                foreach ($tagIds as $tagId) {
                    $pt = new PostTagModel();
                    $pt->note_id = $note->note_id;
                    $pt->tag_id = $tagId;
                    $pt->save();
                }
                return response()->json(['status' => 200, 'data' => $result], 200);
            } else {
                return response()->json(['status' => 400, 'error' => 'Save returned false.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
        }
    }
    public function fetchAllNotes(Request $request) {
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
                $auth = AuthMiddleware::authenticate($request);
                if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }

                $query = NoteModel::query()->join('tbl_category','tbl_note.category_id','=','tbl_category.category_id');
                // If not admin, only show notes owned by the authenticated user
                if ($auth['role_id'] != 1) {
                    $query->where('tbl_note.user_id', $auth['user_id']);
                }
                
                if (isset($data['search'])) { $query->where('tbl_note.title', 'LIKE', '%' . $data['search'] . '%'); }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                $notes = $query->select('tbl_note.*','tbl_category.category_name')->get();
                return response()->json(['status' => 200, 'count' => count($notes), 'data' => $notes], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchSingleNote(Request $request) {
        $valid = Validator::make($request->all(), [
            "note_id" => "required|exists:tbl_note,note_id"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $auth = AuthMiddleware::authenticate($request);
                if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }

                $note = NoteModel::where('note_id', $request->input('note_id'))->first();
                if ($note && $auth['role_id'] != 1 && $note->user_id != $auth['user_id']) {
                    return response()->json(['status' => 403, 'error' => 'Unauthorized: cannot view this note.'], 403);
                }
                return response()->json(['status' => 200, 'data' => $note], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updateNote(Request $request) {
        $valid = Validator::make($request->all(), [
            "note_id" => "required|exists:tbl_note,note_id",
            "user_id" => "sometimes|exists:tbl_user,user_id"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $note = new NoteModel();
            // Verify ownership or admin
            $auth = AuthMiddleware::authenticate($request);
            if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
            $existingNote = $note->where('note_id', $request->input('note_id'))->first();
            if (!$existingNote || ($auth['role_id'] != 1 && $existingNote->user_id != $auth['user_id'])) {
                return response()->json(['status' => 403, 'error' => 'Unauthorized: You can only edit your own notes.'], 403);
            }
            
            try {
                // Handle category_name: get or create by name (with slug)
                if ($request->has('category_name') && !empty($request->input('category_name'))) {
                    $categoryName = $request->input('category_name');
                    $categorySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $categoryName), '-'));
                    $category = CategoryModel::firstOrCreate(
                        ['category_name' => $categoryName],
                        ['category_slug' => $categorySlug, 'category_status' => 1]
                    );
                    $request->request->set('category_id', $category->category_id);
                }
                
                $newrequest = $request->except(['note_id', 'tags', 'user_id', 'category_name']);
                // ensure updated_by is set from session
                $newrequest['updated_by'] = $auth['user_id'];
                
                $result = $note->where('note_id', $request->input('note_id'))->update($newrequest);
                
                // Handle tags: get or create by name
                if ($request->has('tags') && is_array($request->input('tags'))) {
                    PostTagModel::where('note_id', $request->input('note_id'))->delete();
                    
                    foreach ($request->input('tags') as $tagName) {
                        if (!empty($tagName)) {
                            $tag = TagModel::firstOrCreate(
                                ['tag_name' => $tagName],
                                ['tag_status' => 1]
                            );
                            $pt = new PostTagModel();
                            $pt->note_id = $request->input('note_id');
                            $pt->tag_id = $tag->tag_id;
                            $pt->save();
                        }
                    }
                }
                
                return response()->json(['status' => 200, 'data' => $result], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function deleteNote(Request $request) {
        $valid = Validator::make($request->all(), [
            "note_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $note = new NoteModel();
            // Verify ownership or admin
            $auth = AuthMiddleware::authenticate($request);
            if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
            $existingNote = $note->where('note_id', $request->input('note_id'))->first();
            if (!$existingNote || ($auth['role_id'] != 1 && $existingNote->user_id != $auth['user_id'])) {
                return response()->json(['status' => 403, 'error' => 'Unauthorized: You can only delete your own notes.'], 403);
            }
            $request->request->add(['note_status' => 0, 'updated_by' => $auth['user_id']]);
            $newrequest = $request->except(['note_id', 'user_id']);
            try {
                $result = $note->where('note_id', $request->input('note_id'))->update($newrequest);
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