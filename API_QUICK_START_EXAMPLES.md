# API Authorization - Quick Start Examples

## Scenario 1: User Registration & Login

### Step 1: Get Registration Data
```bash
curl -X POST http://localhost:8000/api/user/get-dropdowns
```
Response: Countries and roles list (PUBLIC - no auth needed)

### Step 2: Register New User
```bash
curl -X POST http://localhost:8000/api/user/create \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_author",
    "email": "john@example.com",
    "password": "SecurePassword123",
    "country_id": 1,
    "role_id": 2
  }'
```
Response: User created successfully (no token yet)

### Step 3: Login to Get Token
```bash
curl -X POST http://localhost:8000/api/user/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword123"
  }'
```
Response:
```json
{
    "status": 200,
    "data": {
        "user_id": 5,
        "username": "john_author",
        "email": "john@example.com",
        "role_id": 2
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...ABC123DEF456..."
}
```

---

## Scenario 2: Authorized API Request (Author)

### Create a Post
```bash
curl -X POST http://localhost:8000/api/post/create \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...ABC123DEF456..." \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My First Blog Post",
    "content": "This is the content of my first blog post...",
    "category_name": "Technology",
    "is_public": 1,
    "tags": ["Laravel", "API", "Authentication"]
  }'
```
Response: ✅ Post created successfully (Author can create posts)

### Fetch All Posts
```bash
curl -X POST http://localhost:8000/api/post/fetch-all \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...ABC123DEF456..." \
  -H "Content-Type: application/json" \
  -d '{
    "offset": 0,
    "limit": 10
  }'
```
Response: ✅ Returns all posts (Author can see all + own private posts)

---

## Scenario 3: Authorization Error Examples

### Attempt API Access Without Token
```bash
curl -X POST http://localhost:8000/api/category/fetch-all \
  -H "Content-Type: application/json" \
  -d '{"offset": 0, "limit": 10}'
```
Response: ❌ 401 Error
```json
{
    "status": 401,
    "error": "Authorization required. Please login to access this resource."
}
```

### Contributor Trying to Create Post
```bash
# Login as contributor (role_id = 3)
curl -X POST http://localhost:8000/api/user/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "contributor@example.com",
    "password": "password"
  }'
# Receive token...

# Try to create post
curl -X POST http://localhost:8000/api/post/create \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Trying to create a post",
    "content": "This should fail..."
  }'
```
Response: ❌ 403 Error
```json
{
    "status": 403,
    "error": "Forbidden: Contributors cannot create posts."
}
```

### Non-Admin Trying to Manage Categories
```bash
curl -X POST http://localhost:8000/api/category/create \
  -H "Authorization: Bearer <author-token>" \
  -H "Content-Type: application/json" \
  -d '{"category_name": "New Category"}'
```
Response: ❌ 403 Error
```json
{
    "status": 403,
    "error": "Forbidden: only administrators can create categories."
}
```

---

## Scenario 4: Admin Operations

### Admin Login
```bash
curl -X POST http://localhost:8000/api/user/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "AdminPassword123"
  }'
```
Receives admin token (role_id = 1)

### Admin Fetching All Users
```bash
curl -X POST http://localhost:8000/api/user/fetch-all \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{
    "offset": 0,
    "limit": 50
  }'
```
Response: ✅ Returns all users (Only admin can do this)

### Admin Creating Category
```bash
curl -X POST http://localhost:8000/api/category/create \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"category_name": "Technology"}'
```
Response: ✅ Category created (Only admin can do this)

---

## Scenario 5: Logout

### User Logout
```bash
curl -X POST http://localhost:8000/api/user/logout \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{}'
```
Response: ✅ Logged out successfully
```json
{
    "status": 200,
    "data": "Logged out successfully."
}
```

### Attempt to Use Token After Logout
```bash
curl -X POST http://localhost:8000/api/post/fetch-all \
  -H "Authorization: Bearer <old-token>" \
  -H "Content-Type: application/json" \
  -d '{"offset": 0, "limit": 10}'
```
Response: ❌ 401 Error (Token is no longer valid)
```json
{
    "status": 401,
    "error": "Authorization required. Please login to access this resource."
}
```

---

## Role Permissions Quick Reference

### Admin (role_id = 1)
- ✅ Manage all users
- ✅ Manage categories, tags, roles, countries
- ✅ Create/edit/delete all posts
- ✅ View all content (public + private)
- ✅ Manage all notes

### Author (role_id = 2)
- ✅ Create and manage own posts
- ✅ Create and manage personal notes
- ✅ View all posts (public + own private)
- ✅ Attach/detach tags
- ❌ Cannot create/edit categories, tags, roles
- ❌ Cannot manage other users

### Contributor (role_id = 3)
- ✅ View public posts only
- ❌ Cannot create posts
- ❌ Cannot create/edit notes
- ❌ Cannot access admin features

---

## Key Points

1. **Always include Bearer token** in Authorization header for protected endpoints
2. **Different roles** have different permissions - check error messages
3. **Ownership matters** - authors can only edit/delete their own content (except admin)
4. **Token becomes invalid** after logout
5. **Public posts** can be viewed by anyone via `/api/post/fetch-public`

---

## Debugging Tips

- If getting 401: Check token is valid and not expired
- If getting 403: Check user role has permission for that operation
- If operation silently fails: Check error message in response
- Always include Content-Type: application/json header

---

Generated: May 20, 2026
