@extends('layouts.master')

@section('content')
    <section>
        <h2>Posts</h2>
        <div class="toolbar">
            <select id="postsFilter" class="select"><option value="">All</option></select>
            <button id="refreshPosts" class="btn">Refresh</button>
            <button id="showPostFormBtn" class="btn secondary" style="margin-left:8px">New Post</button>
        </div>

        <form id="postForm" style="display:none;margin-top:12px;max-width:720px;background:#fff;padding:12px;border-radius:6px">
            <input type="hidden" name="post_id" />
            <div><label>Title</label><input name="title" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
            <div style="margin-top:8px"><label>Content</label><textarea name="content" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6;height:120px"></textarea></div>
            <div style="margin-top:8px"><label>Category</label><input name="category_name" placeholder="Category name" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
            <div style="margin-top:8px"><label>Tags (comma separated)</label><input name="tags" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
            <div style="margin-top:8px"><label><input type="checkbox" name="is_public" checked/> Public</label></div>
            <div style="margin-top:12px"><button class="btn" type="submit">Save Post</button><button type="button" id="cancelPostForm" class="btn secondary" style="margin-left:8px">Cancel</button></div>
        </form>

        <div id="postsContainer" class="list"></div>
    </section>
@endsection
