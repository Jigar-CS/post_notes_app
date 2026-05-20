# POSTMAN TEST GUIDE

Quick Postman guide to verify auth, RBAC, and CRUD behavior in this backend.

Base URL (example):
`{{BASE_URL}} = http://localhost/mini_blog_notes/public/index.php/api`

Important: Current implementation requires both a Bearer token and the PHP session cookie. Login returns `token` and sets `PHPSESSID` cookie.

1) Get registration dropdowns (public)
- Method: POST
- URL: `{{BASE_URL}}/user/get-dropdowns`
- Body: none
- Expect: 200 JSON with `countries` and `roles` arrays

2) Create / Register a user
- Method: POST
- URL: `{{BASE_URL}}/user/create`
- Body (JSON):
```
{
  "username": "alice",
  "email": "alice@example.com",
  "password": "secret123",
  "country_id": 1,
  "role_id": 2
}
```

3) Login (generates token + session cookie)
- Method: POST
- URL: `{{BASE_URL}}/user/login`
- Body (JSON):
```
{
  "email": "alice@example.com",
  "password": "secret123"
}
```

Postman Tests tab (copy into Login → Tests):
```
pm.test('Login responded 200', () => pm.response.code === 200);
const json = pm.response.json();
pm.test('Token present', () => json.token && json.token.length > 0);
pm.environment.set('token', json.token);
const php = pm.cookies.get('PHPSESSID');
if (php) pm.environment.set('php_session', php);
```

4) Use a protected endpoint (example: fetch all posts)
- Method: POST
- URL: `{{BASE_URL}}/post/fetch-all`
- Headers:
  - `Authorization: Bearer {{token}}`
  - Cookie: `PHPSESSID={{php_session}}` (Postman usually handles cookie automatically)
- Body (JSON): `{}` or add `offset`, `limit`, `search`

5) Verify unauthorized access
- Remove Authorization header and clear cookie (or create a new request with no auth)
- Expect: 401 JSON `{status:401, error: 'Authorization required.'}`

6) RBAC checks
- Create two users: one admin (`role_id: 1`) and one normal user (`role_id: 2`)
- Login as normal user, call `post/fetch-all` → should only return posts owned by that user (not other users)
- Login as admin, call `post/fetch-all` → should return all posts

To assert RBAC in Postman Tests, add assertions like:
```
// After fetch-all
const body = pm.response.json();
pm.test('Response ok', () => pm.response.code === 200);
// add checks on returned items to match expected owner id
```

7) Logout
- Method: POST
- URL: `{{BASE_URL}}/user/logout`
- Headers: `Authorization: Bearer {{token}}`, Cookie: `PHPSESSID={{php_session}}`

Tests tab for logout to clean environment:
```
pm.test('Logout 200', () => pm.response.code === 200);
pm.environment.unset('token');
pm.environment.unset('php_session');
```

Notes & troubleshooting
- Ensure Postman saves the `PHPSESSID` cookie set on login — check the Cookies tab for your host.
- Requests must include the Bearer token in `Authorization` header and the `PHPSESSID` cookie because the server compares the header token to the session token.
- If you prefer stateless tokens (persisted in DB and valid independently of PHP session), I can change auth to store tokens in `tbl_user` or a dedicated `tbl_api_tokens` table.

Optional: I can export a Postman Collection file and add it to the repo. Reply "export collection" and I'll add it under `postman_collection.json`.

# Complete Postman Testing Guide for Mini Blog & Notes

## Base URL
```
http://127.0.0.1:8000/api
```

---

## 1. USER MANAGEMENT

### 1.1 Register User
**Endpoint:** `POST /user/create`

```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123",
  "country_id": 1,
  "role_id": 1
}
```

**Expected Response:**
```json
{
  "status": 200,
  "data": true
}
```

---

### 1.2 Register Second User
**Endpoint:** `POST /user/create`

```json
{
  "username": "jane_smith",
  "email": "jane@example.com",
  "password": "password456",
  "country_id": 2,
  "role_id": 2
}
```

---

### 1.3 Get Dropdowns (Countries, Ages, Roles)
**Endpoint:** `POST /user/get-dropdowns`

**Body:** `{}` (empty)

**Expected Response:**
```json
{
  "status": 200,
  "data": {
    "countries": [
      {
        "country_id": 1,
        "country_name": "USA",
        "country_status": 1
      },
      {
        "country_id": 2,
        "country_name": "India",
        "country_status": 1
      }
    ],
    /* ages removed */
    "roles": [
      {
        "role_id": 1,
        "role_name": "Admin",
        "role_status": 1
      }
    ]
  }
}
```

---

### 1.4 Login User
**Endpoint:** `POST /user/login`

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Expected Response:**
```json
{
  "status": 200,
  "data": {
    "user_id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "country_id": 1,
    "role_id": 1,
    "user_status": 1
  },
  "token": "random_token_string_here"
}
```

**Save user_id (1) and token for later use**

---

### 1.5 Fetch All Users
**Endpoint:** `POST /user/fetch-all`

```json
{
  "search": "john",
  "offset": 0,
  "limit": 10
}
```

**Expected Response:**
```json
{
  "status": 200,
  "count": 1,
  "data": [
    {
      "user_id": 1,
      "username": "john_doe",
      "email": "john@example.com",
      "country_id": 1,
      "country_name": "USA",
      "role_id": 1,
      "role_name": "Admin"
    }
  ]
}
```

---

### 1.6 Fetch Single User
**Endpoint:** `POST /user/fetch-single`

```json
{
  "user_id": 1
}
```

---

## 2. CATEGORY MANAGEMENT

### 2.1 Create Category
**Endpoint:** `POST /category/create`

```json
{
  "category_name": "Technology",
  "category_description": "All tech-related posts"
}
```

**Expected Response:**
```json
{
  "status": 200,
  "data": true
}
```

**Save category_id returned or note it as 1**

---

### 2.2 Create More Categories
**Endpoint:** `POST /category/create`

```json
{
  "category_name": "Lifestyle",
  "category_description": "Lifestyle and wellness"
}
```

```json
{
  "category_name": "Business",
  "category_description": "Business and entrepreneurship"
}
```

---

### 2.3 Fetch All Categories
**Endpoint:** `POST /category/fetch-all`

```json
{
  "search": "",
  "offset": 0,
  "limit": 10
}
```

**Expected Response:**
```json
{
  "status": 200,
  "count": 3,
  "data": [
    {
      "category_id": 1,
      "category_name": "Technology",
      "category_slug": "technology",
      "category_description": "All tech-related posts",
      "category_status": 1
    }
  ]
}
```

---

### 2.4 Fetch Single Category
**Endpoint:** `POST /category/fetch-single`

```json
{
  "category_id": 1
}
```

---

### 2.5 Update Category
**Endpoint:** `POST /category/update`

```json
{
  "category_id": 1,
  "category_name": "Tech & Programming",
  "category_description": "Updated description"
}
```

---

### 2.6 Delete Category
**Endpoint:** `POST /category/delete`

```json
{
  "category_id": 1
}
```

**Expected Response:**
```json
{
  "status": 200,
  "data": {...}
}
```

---

## 3. TAG MANAGEMENT

### 3.1 Create Tags
**Endpoint:** `POST /tag/create`

**Tag 1:**
```json
{
  "tag_name": "Laravel"
}
```

**Tag 2:**
```json
{
  "tag_name": "PHP"
}
```

**Tag 3:**
```json
{
  "tag_name": "Database"
}
```

**Tag 4:**
```json
{
  "tag_name": "API"
}
```

---

### 3.2 Fetch All Tags
**Endpoint:** `POST /tag/fetch-all`

```json
{
  "search": "",
  "offset": 0,
  "limit": 10
}
```

**Expected Response:**
```json
{
  "status": 200,
  "count": 4,
  "data": [
    {
      "tag_id": 1,
      "tag_name": "Laravel",
      "tag_status": 1
    }
  ]
}
```

---

### 3.3 Fetch Single Tag
**Endpoint:** `POST /tag/fetch-single`

```json
{
  "tag_id": 1
}
```

---

### 3.4 Update Tag
**Endpoint:** `POST /tag/update`

```json
{
  "tag_id": 1,
  "tag_name": "Laravel Framework"
}
```

---

### 3.5 Delete Tag
**Endpoint:** `POST /tag/delete`

```json
{
  "tag_id": 1
}
```

---

## 4. POST MANAGEMENT (Blog Posts)

### 4.1 Create Public Post (By John)
**Endpoint:** `POST /post/create`

```json
{
  "user_id": 1,
  "category_id": 2,
  "title": "Getting Started with Laravel",
  "content": "Laravel is a powerful PHP framework for building web applications. In this post, we'll explore the basics of Laravel development.",
  "is_public": 1
}
```

**Expected Response:**
```json
{
  "status": 200,
  "data": true
}
```

**Note:** Save post_id as 1

---

### 4.2 Create Private Post (By John)
**Endpoint:** `POST /post/create`

```json
{
  "user_id": 1,
  "category_id": 3,
  "title": "My Private Thoughts",
  "content": "This is a private post that only John should see",
  "is_public": 0
}
```

---

### 4.3 Create Public Post with Image Upload (By John)
**Endpoint:** `POST /post/create`

**Type:** form-data (NOT JSON)

**Fields:**
```
user_id: 1
category_id: 2
title: "Web Development Best Practices"
content: "Learn the best practices for web development in 2026"
is_public: 1
featured_image: [SELECT AN IMAGE FILE FROM YOUR COMPUTER]
```

---

### 4.4 Create Post by Jane
**Endpoint:** `POST /post/create`

```json
{
  "user_id": 2,
  "category_id": 2,
  "title": "Database Optimization Tips",
  "content": "Learn how to optimize your database queries for better performance",
  "is_public": 1
}
```

---

### 4.5 Fetch All Posts (Admin View - All Posts)
**Endpoint:** `POST /post/fetch-all`

```json
{
  "search": "",
  "offset": 0,
  "limit": 10
}
```

**Expected Response:**
```json
{
  "status": 200,
  "count": 3,
  "data": [
    {
      "post_id": 1,
      "user_id": 1,
      "category_id": 2,
      "title": "Getting Started with Laravel",
      "content": "Laravel is a powerful...",
      "featured_image": null,
      "is_public": 1,
      "post_status": 1,
      "username": "john_doe",
      "category_name": "Technology"
    }
  ]
}
```

---

### 4.6 Fetch Public Posts (Public View - No Auth Required)
**Endpoint:** `POST /post/fetch-public`

```json
{
  "search": "",
  "offset": 0,
  "limit": 10
}
```

**Expected Response:** Only posts with `is_public: 1`

---

### 4.7 Fetch Single Post
**Endpoint:** `POST /post/fetch-single`

```json
{
  "post_id": 1
}
```

---

### 4.8 Update Post (By Owner)
**Endpoint:** `POST /post/update`

```json
{
  "post_id": 1,
  "user_id": 1,
  "title": "Getting Started with Laravel - Updated",
  "content": "Updated content here...",
  "is_public": 1
}
```

**Expected Response:** 200 OK

---

### 4.9 Try to Update Post (By Non-Owner) - SHOULD FAIL
**Endpoint:** `POST /post/update`

```json
{
  "post_id": 1,
  "user_id": 2,
  "title": "Hacked Title",
  "content": "Hacked content"
}
```

**Expected Response:**
```json
{
  "status": 403,
  "error": "Unauthorized: You can only edit your own posts."
}
```

---

### 4.10 Delete Post (By Owner)
**Endpoint:** `POST /post/delete`

```json
{
  "post_id": 2,
  "user_id": 1
}
```

**Expected Response:** 200 OK

---

### 4.11 Try to Delete Post (By Non-Owner) - SHOULD FAIL
**Endpoint:** `POST /post/delete`

```json
{
  "post_id": 1,
  "user_id": 2
}
```

**Expected Response:**
```json
{
  "status": 403,
  "error": "Unauthorized: You can only delete your own posts."
}
```

---

## 5. NOTE MANAGEMENT (Personal Notes)

### 5.1 Create Note (By John)
**Endpoint:** `POST /note/create`

```json
{
  "user_id": 1,
  "category_id": 2,
  "title": "Meeting Notes",
  "content": "Important points from today's meeting..."
}
```

**Expected Response:**
```json
{
  "status": 200,
  "data": true
}
```

---

### 5.2 Create More Notes (By John)
**Endpoint:** `POST /note/create`

```json
{
  "user_id": 1,
  "category_id": 3,
  "title": "Project Ideas",
  "content": "Ideas for upcoming projects..."
}
```

**Endpoint:** `POST /note/create`

```json
{
  "user_id": 1,
  "category_id": 2,
  "title": "Learning Progress",
  "content": "Tracking my learning progress in Laravel..."
}
```

---

### 5.3 Create Note (By Jane)
**Endpoint:** `POST /note/create`

```json
{
  "user_id": 2,
  "category_id": 2,
  "title": "Jane's Notes",
  "content": "Jane's personal notes..."
}
```

---

### 5.4 Fetch All Notes (By John - Only His Notes)
**Endpoint:** `POST /note/fetch-all`

```json
{
  "user_id": 1,
  "search": "",
  "offset": 0,
  "limit": 10
}
```

**Expected Response:** Only notes created by John

```json
{
  "status": 200,
  "count": 3,
  "data": [
    {
      "note_id": 1,
      "user_id": 1,
      "category_id": 2,
      "title": "Meeting Notes",
      "content": "Important points...",
      "note_status": 1,
      "category_name": "Technology"
    }
  ]
}
```

---

### 5.5 Try to Fetch Notes Without user_id - SHOULD FAIL
**Endpoint:** `POST /note/fetch-all`

```json
{
  "search": "",
  "offset": 0,
  "limit": 10
}
```

**Expected Response:**
```json
{
  "status": 400,
  "error": {
    "user_id": ["The user_id field is required."]
  }
}
```

---

### 5.6 Fetch Single Note
**Endpoint:** `POST /note/fetch-single`

```json
{
  "note_id": 1
}
```

---

### 5.7 Update Note (By Owner)
**Endpoint:** `POST /note/update`

```json
{
  "note_id": 1,
  "user_id": 1,
  "title": "Meeting Notes - Updated",
  "content": "Updated meeting notes..."
}
```

**Expected Response:** 200 OK

---

### 5.8 Try to Update Note (By Non-Owner) - SHOULD FAIL
**Endpoint:** `POST /note/update`

```json
{
  "note_id": 1,
  "user_id": 2,
  "title": "Hacked Note",
  "content": "Hacked content"
}
```

**Expected Response:**
```json
{
  "status": 403,
  "error": "Unauthorized: You can only edit your own notes."
}
```

---

### 5.9 Delete Note (By Owner)
**Endpoint:** `POST /note/delete`

```json
{
  "note_id": 2,
  "user_id": 1
}
```

**Expected Response:** 200 OK

---

### 5.10 Try to Delete Note (By Non-Owner) - SHOULD FAIL
**Endpoint:** `POST /note/delete`

```json
{
  "note_id": 1,
  "user_id": 2
}
```

**Expected Response:**
```json
{
  "status": 403,
  "error": "Unauthorized: You can only delete your own notes."
}
```

---

## 6. TAG ATTACHMENT (Post-Tag Relations)

### 6.1 Attach Tags to Post
**Endpoint:** `POST /post-tag/attach`

```json
{
  "post_id": 1,
  "tag_id": 2
}
```

---

### 6.2 Attach Another Tag
**Endpoint:** `POST /post-tag/attach`

```json
{
  "post_id": 1,
  "tag_id": 3
}
```

---

### 6.3 Detach Tag from Post
**Endpoint:** `POST /post-tag/detach`

```json
{
  "post_id": 1,
  "tag_id": 2
}
```

---

## 7. MASTER DATA (Countries, Ages, Roles)

### 7.1 Create Country
**Endpoint:** `POST /country/create`

```json
{
  "country_name": "Canada",
  "country_code": "CA"
}
```

---

### 7.2 Fetch All Countries
**Endpoint:** `POST /country/fetch-all`

```json
{
  "search": "",
  "offset": 0,
  "limit": 10
}
```

---

### 7.3 Fetch Single Country
**Endpoint:** `POST /country/fetch-single`

```json
{
  "country_id": 1
}
```

---

### 7.4 Update Country
**Endpoint:** `POST /country/update`

```json
{
  "country_id": 1,
  "country_name": "United States of America"
}
```

---

### 7.5 Delete Country
**Endpoint:** `POST /country/delete`

```json
{
  "country_id": 1
}
```

---

## TESTING FLOW SUMMARY

### Step-by-Step Testing Order:

1. **User Registration**
   - Register John: `POST /user/create` ✓
   - Register Jane: `POST /user/create` ✓

2. **User Authentication**
   - Login John: `POST /user/login` ✓ (Get user_id: 1)
   - Login Jane: `POST /user/login` ✓ (Get user_id: 2)

3. **Master Data Setup**
   - Get Dropdowns: `POST /user/get-dropdowns` ✓
   - Create Categories: `POST /category/create` (3 categories) ✓
   - Create Tags: `POST /tag/create` (4 tags) ✓

4. **Blog Post Management**
   - Create public post (John): `POST /post/create` ✓
   - Create private post (John): `POST /post/create` ✓
   - Create post with image (John): `POST /post/create` ✓
   - Create public post (Jane): `POST /post/create` ✓
   - Fetch all posts: `POST /post/fetch-all` ✓
   - Fetch public posts (no auth): `POST /post/fetch-public` ✓
   - Update own post: `POST /post/update` ✓
   - Try update others post (should fail): `POST /post/update` ✗
   - Delete own post: `POST /post/delete` ✓
   - Try delete others post (should fail): `POST /post/delete` ✗

5. **Personal Notes Management**
   - Create notes (John): `POST /note/create` (3 notes) ✓
   - Create notes (Jane): `POST /note/create` (1 note) ✓
   - Fetch John's notes only: `POST /note/fetch-all` ✓
   - Try fetch without user_id (should fail): `POST /note/fetch-all` ✗
   - Update own note: `POST /note/update` ✓
   - Try update others note (should fail): `POST /note/update` ✗
   - Delete own note: `POST /note/delete` ✓
   - Try delete others note (should fail): `POST /note/delete` ✗

6. **Tag Management**
   - Attach tags to post: `POST /post-tag/attach` ✓
   - Detach tags: `POST /post-tag/detach` ✓

---

## KEY SECURITY TESTS

- ✓ User can only edit/delete their own posts
- ✓ User can only edit/delete their own notes
- ✓ User can only fetch their own notes (user_id required)
- ✓ Public posts visible without authentication
- ✓ Private posts (is_public=0) not shown in public feed
- ✓ 403 Unauthorized returned for unauthorized operations
- ✓ Proper validation errors returned

---

## DATA IDS TO USE

```
User IDs:
- John: user_id = 1
- Jane: user_id = 2

Category IDs:
- Technology: category_id = 2
- Lifestyle: category_id = 3
- Business: category_id = 4

Tag IDs:
- Laravel: tag_id = 1
- PHP: tag_id = 2
- Database: tag_id = 3
- API: tag_id = 4

Post IDs: 1, 2, 3, 4 (as created)
Note IDs: 1, 2, 3, 4 (as created)
```

---

## POSTMAN COLLECTION JSON

Import this collection directly into Postman for quick setup:

**File:** `mini_blog_notes_collection.json` (Create separately with all requests)

Alternatively, create requests manually following the examples above.
