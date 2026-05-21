@extends('layouts.master')

@section('page', 'dashboard')

@section('content')
<section class="hero-banner">
    <p class="eyebrow">Control Panel</p>
    <h1>Mini Blog Notes Dashboard</h1>
    <p>Manage posts, notes, users, and all master data from one place.</p>
    <div class="hero-actions">
        <button class="btn" id="refreshDashboardBtn" type="button">Refresh All</button>
    </div>
</section>

<section class="panel" id="authPanel">
    <h2>Session</h2>
    <div class="grid-two">
        <div class="card-block">
            <h3>Current User</h3>
            <div id="sessionUserView" class="code-view">Not logged in</div>
        </div>
        <div class="card-block">
            <h3>Quick Actions</h3>
            <div class="stack-inline">
                <a class="btn secondary" href="{{ url('/login') }}">Login</a>
                <a class="btn secondary" href="{{ url('/register') }}">Register</a>
                <button class="btn danger" id="dashboardLogoutBtn" type="button">Logout</button>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <h2>Posts</h2>
    <form id="postForm" class="entity-form" enctype="multipart/form-data">
        <input type="hidden" name="post_id">
        <div class="form-grid">
            <label>Title<input type="text" name="title" required></label>
            <label>Category Name<input type="text" name="category_name" required></label>
            <label>Public
                <select name="is_public">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </label>
            <label>Featured Image<input type="file" name="featured_image" accept="image/*"></label>
        </div>
        <label>Content<textarea name="content" rows="4" required></textarea></label>
        <label>Tags (comma separated)<input type="text" name="tags" placeholder="php, laravel"></label>
        <div class="stack-inline">
            <button class="btn" type="submit">Save Post</button>
            <button class="btn secondary" id="postFormReset" type="button">Clear</button>
        </div>
    </form>
    <div class="toolbar-row">
        <input type="text" id="postSearch" placeholder="Search posts">
        <button class="btn secondary" id="fetchPostsBtn" type="button">Fetch Posts</button>
    </div>
    <div id="postsTableWrap" class="table-wrap"></div>
</section>

<section class="panel">
    <h2>Notes</h2>
    <form id="noteForm" class="entity-form">
        <input type="hidden" name="note_id">
        <div class="form-grid">
            <label>Title<input type="text" name="title" required></label>
            <label>Category Name<input type="text" name="category_name" required></label>
        </div>
        <label>Content<textarea name="content" rows="4" required></textarea></label>
        <label>Tags (comma separated)<input type="text" name="tags" placeholder="daily, task"></label>
        <div class="stack-inline">
            <button class="btn" type="submit">Save Note</button>
            <button class="btn secondary" id="noteFormReset" type="button">Clear</button>
        </div>
    </form>
    <div class="toolbar-row">
        <input type="text" id="noteSearch" placeholder="Search notes">
        <button class="btn secondary" id="fetchNotesBtn" type="button">Fetch Notes</button>
    </div>
    <div id="notesTableWrap" class="table-wrap"></div>
</section>

<section class="panel admin-only">
    <h2>Users (Admin)</h2>
    <form id="userForm" class="entity-form">
        <input type="hidden" name="user_id">
        <div class="form-grid">
            <label>Username<input type="text" name="username"></label>
            <label>Email<input type="email" name="email"></label>
            <label>Password<input type="password" name="password"></label>
            <label>Country ID<input type="number" name="country_id" min="1"></label>
            <label>Role ID<input type="number" name="role_id" min="1"></label>
            <label>Status
                <select name="user_status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </label>
        </div>
        <div class="stack-inline">
            <button class="btn" type="submit">Save User</button>
            <button class="btn secondary" id="userFormReset" type="button">Clear</button>
        </div>
    </form>
    <div class="toolbar-row">
        <input type="text" id="userSearch" placeholder="Search users">
        <button class="btn secondary" id="fetchUsersBtn" type="button">Fetch Users</button>
    </div>
    <div id="usersTableWrap" class="table-wrap"></div>
</section>

<section class="panel admin-only">
    <h2>Categories (Admin)</h2>
    <form id="categoryForm" class="entity-form compact">
        <input type="hidden" name="category_id">
        <div class="form-grid">
            <label>Category Name<input type="text" name="category_name" required></label>
            <label>Status
                <select name="category_status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </label>
        </div>
        <div class="stack-inline">
            <button class="btn" type="submit">Save Category</button>
            <button class="btn secondary" id="categoryFormReset" type="button">Clear</button>
        </div>
    </form>
    <div id="categoriesTableWrap" class="table-wrap"></div>
</section>

<section class="panel admin-only">
    <h2>Tags (Admin)</h2>
    <form id="tagForm" class="entity-form compact">
        <input type="hidden" name="tag_id">
        <div class="form-grid">
            <label>Tag Name<input type="text" name="tag_name" required></label>
            <label>Status
                <select name="tag_status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </label>
        </div>
        <div class="stack-inline">
            <button class="btn" type="submit">Save Tag</button>
            <button class="btn secondary" id="tagFormReset" type="button">Clear</button>
        </div>
    </form>
    <div id="tagsTableWrap" class="table-wrap"></div>
</section>

<section class="panel admin-only">
    <h2>Countries (Admin)</h2>
    <form id="countryForm" class="entity-form compact">
        <input type="hidden" name="country_id">
        <div class="form-grid">
            <label>Country Name<input type="text" name="country_name" required></label>
            <label>Status
                <select name="country_status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </label>
        </div>
        <div class="stack-inline">
            <button class="btn" type="submit">Save Country</button>
            <button class="btn secondary" id="countryFormReset" type="button">Clear</button>
        </div>
    </form>
    <div id="countriesTableWrap" class="table-wrap"></div>
</section>

<section class="panel admin-only">
    <h2>Roles (Admin)</h2>
    <form id="roleForm" class="entity-form compact">
        <input type="hidden" name="role_id">
        <div class="form-grid">
            <label>Role Name<input type="text" name="role_name" required></label>
            <label>Status
                <select name="role_status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </label>
        </div>
        <div class="stack-inline">
            <button class="btn" type="submit">Save Role</button>
            <button class="btn secondary" id="roleFormReset" type="button">Clear</button>
        </div>
    </form>
    <div id="rolesTableWrap" class="table-wrap"></div>
</section>

<section class="panel">
    <h2>Post Tag Attach/Detach</h2>
    <div class="grid-two">
        <form id="attachTagForm" class="entity-form compact">
            <h3>Attach Tag</h3>
            <label>Post ID (optional)<input type="number" name="post_id" min="1"></label>
            <label>Note ID (optional)<input type="number" name="note_id" min="1"></label>
            <label>Tag ID<input type="number" name="tag_id" min="1" required></label>
            <button class="btn" type="submit">Attach</button>
        </form>
        <form id="detachTagForm" class="entity-form compact">
            <h3>Detach Tag</h3>
            <label>Post Tag ID<input type="number" name="post_tag_id" min="1" required></label>
            <button class="btn danger" type="submit">Detach</button>
        </form>
    </div>
</section>
@endsection