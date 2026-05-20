<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\PostModel;
use App\Models\PostTagModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Http\Controllers\AuthMiddleware;
class PostModelController extends Controller {
    public function createPost(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        
        // Validation: accept category_name and tag names only
        $valid = Validator::make($request->all(), [
            "title" => "required",
            "content" => "required",
            "category_name" => "required|string",
            "is_public" => "nullable|integer|in:0,1",
            "user_id" => "nullable|exists:tbl_user,user_id",
            "tags" => "nullable|array",
            "tags.*" => "nullable|string"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        }
        
        try {
            // Contributors cannot create posts
            if ($auth['role_id'] == 3) {
                return response()->json(['status' => 403, 'error' => 'Forbidden: Contributors cannot create posts.'], 403);
            }
            
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
            
            $post = new PostModel();
            // Determine user for the post: admin may set `user_id`, others use their own id
            $userId = $auth['user_id'];
            if ($auth['role_id'] == 1 && $request->has('user_id')) {
                $userId = $request->input('user_id');
            }
            $post->user_id = $userId;
            // audit
            $post->created_by = $auth['user_id'];
            $post->category_id = $categoryId;
            $post->title = $request->input('title');
            $post->content = $request->input('content');
            $post->is_public = $request->input('is_public', 1);
            $post->post_status = 1;
            if ($request->hasFile('featured_image')) {
                $imgName = time().'_'.$request->file('featured_image')->getClientOriginalName();
                $request->file('featured_image')->move(public_path('uploads/posts'), $imgName);
                $post->featured_image = $imgName;
            }
            
            $result = $post->save();
            if ($result) {
                // Attach resolved tags
                foreach ($tagIds as $tagId) {
                    $pt = new PostTagModel();
                    $pt->post_id = $post->post_id;
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
    public function fetchAllPosts(Request $request) {
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

                $query = PostModel::query()->join('tbl_user','tbl_post.user_id','=','tbl_user.user_id')->join('tbl_category','tbl_post.category_id','=','tbl_category.category_id');
                // Admin sees all
                if ($auth['role_id'] == 1) {
                    // no extra where
                } elseif ($auth['role_id'] == 2) {
                    // Author: own posts + public posts
                    $query->where(function($q) use ($auth) {
                        $q->where('tbl_post.is_public', 1)
                          ->orWhere('tbl_post.user_id', $auth['user_id']);
                    });
                } else {
                    // Contributor: only public posts
                    $query->where('tbl_post.is_public', 1);
                }
                if (isset($data['search'])) { $query->where('tbl_post.title', 'LIKE', '%' . $data['search'] . '%'); }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                $posts = $query->select('tbl_post.*','tbl_user.username','tbl_category.category_name')->get();
                return response()->json(['status' => 200, 'count' => count($posts), 'data' => $posts], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchPublicPosts(Request $request) {
        $valid = Validator::make($request->all(), []);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $data = array();
            if ($request->has('offset') && $request->filled('offset')) { $data["offset"] = $request->input("offset"); }
            if ($request->has('limit') && $request->filled('limit')) { $data["limit"] = $request->input("limit"); }
            if ($request->has('search') && $request->input('search') != "") { $data['search'] = $request->input('search'); }
            try {
                $query = PostModel::query()->join('tbl_user','tbl_post.user_id','=','tbl_user.user_id')->join('tbl_category','tbl_post.category_id','=','tbl_category.category_id');
                // Only show public posts with is_public = 1
                $query->where('tbl_post.is_public', 1);
                $query->where('tbl_post.post_status', 1);
                if (isset($data['search'])) { $query->where('tbl_post.title', 'LIKE', '%' . $data['search'] . '%'); }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                $posts = $query->select('tbl_post.*','tbl_user.username','tbl_category.category_name')->get();
                return response()->json(['status' => 200, 'count' => count($posts), 'data' => $posts], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchSinglePost(Request $request) {
        $valid = Validator::make($request->all(), [
            "post_id" => "required|exists:tbl_post,post_id"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $auth = AuthMiddleware::authenticate($request);
                if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }

                $post = PostModel::where('post_id', $request->input('post_id'))->first();
                if (!$post) {
                    return response()->json(['status' => 404, 'error' => 'Post not found.'], 404);
                }
                // Admin can view any
                if ($auth['role_id'] == 1) {
                    // ok
                } elseif ($post->is_public == 1) {
                    // public post ok for all
                } elseif ($post->user_id == $auth['user_id']) {
                    // owner ok
                } else {
                    return response()->json(['status' => 403, 'error' => 'Unauthorized: cannot view this post.'], 403);
                }
                return response()->json(['status' => 200, 'data' => $post], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updatePost(Request $request) {
        $valid = Validator::make($request->all(), [
            "post_id" => "required|exists:tbl_post,post_id",
            "user_id" => "sometimes|exists:tbl_user,user_id"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $post = new PostModel();
            // Verify ownership or admin
            $auth = AuthMiddleware::authenticate($request);
            if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
            $existingPost = $post->where('post_id', $request->input('post_id'))->first();
            if (!$existingPost) { return response()->json(['status' => 404, 'error' => 'Post not found.'], 404); }
            // Contributors cannot update posts
            if ($auth['role_id'] == 3) { return response()->json(['status' => 403, 'error' => 'Forbidden: Contributors cannot update posts.'], 403); }
            if ($auth['role_id'] != 1 && $existingPost->user_id != $auth['user_id']) {
                return response()->json(['status' => 403, 'error' => 'Unauthorized: You can only edit your own posts.'], 403);
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
                
                $newrequest = $request->except(['post_id', 'tags', 'user_id', 'category_name']);
                if ($request->hasFile('featured_image')) {
                    $imgName = time().'_'.$request->file('featured_image')->getClientOriginalName();
                    $request->file('featured_image')->move(public_path('uploads/posts'), $imgName);
                    $newrequest['featured_image'] = $imgName;
                }
                
                // ensure updated_by is set from session
                $newrequest['updated_by'] = $auth['user_id'];
                $result = $post->where('post_id', $request->input('post_id'))->update($newrequest);
                
                // Handle tags: get or create by name
                if ($request->has('tags') && is_array($request->input('tags'))) {
                    PostTagModel::where('post_id', $request->input('post_id'))->delete();
                    
                    foreach ($request->input('tags') as $tagName) {
                        if (!empty($tagName)) {
                            $tag = TagModel::firstOrCreate(
                                ['tag_name' => $tagName],
                                ['tag_status' => 1]
                            );
                            $pt = new PostTagModel();
                            $pt->post_id = $request->input('post_id');
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
    public function deletePost(Request $request) {
        $valid = Validator::make($request->all(), [
            "post_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $post = new PostModel();
            // Verify ownership or admin
            $auth = AuthMiddleware::authenticate($request);
            if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
            $existingPost = $post->where('post_id', $request->input('post_id'))->first();
            if (!$existingPost) { return response()->json(['status' => 404, 'error' => 'Post not found.'], 404); }
            // Contributors cannot delete posts
            if ($auth['role_id'] == 3) { return response()->json(['status' => 403, 'error' => 'Forbidden: Contributors cannot delete posts.'], 403); }
            if ($auth['role_id'] != 1 && $existingPost->user_id != $auth['user_id']) {
                return response()->json(['status' => 403, 'error' => 'Unauthorized: You can only delete your own posts.'], 403);
            }
            $request->request->add(['post_status' => 0, 'updated_by' => $auth['user_id']]);
            $newrequest = $request->except(['post_id', 'user_id']);
            try {
                $result = $post->where('post_id', $request->input('post_id'))->update($newrequest);
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