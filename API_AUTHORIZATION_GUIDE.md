# API Authorization & Role-Based Access Control (RBAC) Documentation

## Overview

This Mini Blog application implements **token-based authentication** with **role-based access control (RBAC)**. After user registration, authentication via login and token is required for all protected endpoints. All unauthorized access attempts will receive a `401 Authorization Error` or `403 Forbidden Error`.

---

## Authentication System

### Token-Based Authentication

- **Token Generation**: Tokens are generated on successful login (80-character random string)
- **Token Storage**: Tokens are stored in the `tbl_user.api_token` database column
- **Token Transmission**: Tokens are sent via HTTP `Authorization` header with `Bearer` scheme
- **Session Support**: Both session-based and bearer token authentication are supported

### How Authentication Works

1. User registers via `/user/create`
2. User logs in via `/user/login` → receives API token
3. User includes token in all subsequent requests:
   ```
   Authorization: Bearer <token>
   ```
4. Server validates token and identifies user
5. User logs out via `/user/logout` → token is cleared

### Example Login Request

```bash
POST /api/user/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "status": 200,
    "data": {
        "user_id": 1,
        "username": "john_doe",
        "email": "user@example.com",
        "role_id": 2
    },
    "token": "kL9mN2pQ3rS4tU5vW6xY7zA8bC9dE0fG1hI2jK3lM4nO5pQ6rS7tU8vW9xY0zA"
}
```

### Example API Request with Token

```bash
POST /api/post/create
Authorization: Bearer kL9mN2pQ3rS4tU5vW6xY7zA8bC9dE0fG1hI2jK3lM4nO5pQ6rS7tU8vW9xY0zA
Content-Type: application/json

{
    "title": "My First Post",
    "content": "Post content here...",
    "category_name": "Technology"
}
```

---

## Role Hierarchy & Permissions

### Three User Roles

#### 1. **Admin** (role_id = 1)
- **Full System Access**
- Manage all users (create, read, update, delete)
- Manage all categories, tags, countries, roles
- Create and manage all posts (including for other users)
- Manage all notes
- Attach/detach tags to posts and notes
- View all content (public and private)

#### 2. **Author** (role_id = 2)
- **Content Creator Role**
- Create public and private posts
- Manage own posts and notes
- Cannot manage other users' posts or notes
- Cannot access user/category/tag/role management endpoints
- Can view all public posts + their own posts
- Can attach/detach tags to their own content

#### 3. **Contributor** (role_id = 3)
- **Read-Only Role**
- Can only view public posts and public notes
- Cannot create posts or notes
- Cannot update or delete any content
- Cannot manage any system resources

---

## Authorization Error Responses

### 1. Unauthorized (401)
User is not authenticated or token is invalid/expired.

```json
{
    "status": 401,
    "error": "Authorization required. Please login to access this resource."
}
```

### 2. Forbidden (403)
User is authenticated but lacks required permissions.

```json
{
    "status": 403,
    "error": "Forbidden: only administrators can create categories."
}
```

### 3. Not Found (404)
Resource does not exist.

```json
{
    "status": 404,
    "error": "Post not found."
}
```

---

## Protected vs Public Endpoints

### Public Endpoints (No Authentication Required)

```
POST /api/post/fetch-public          - View public posts
POST /api/user/get-dropdowns         - Get registration form data (countries, roles)
```

### Authentication & Registration (No Token Required)

```
POST /api/user/create                - Register new user
POST /api/user/login                 - Login and get token
```

### Protected Endpoints (Authentication Required)

All other endpoints require a valid Bearer token.

#### User Management (Admin Only)

```
POST /api/user/logout                - Logout (clear token)
POST /api/user/fetch-all             - Fetch all users
POST /api/user/fetch-single          - Fetch single user
POST /api/user/update                - Update user (own or any if admin)
POST /api/user/delete                - Delete user (admin only)
```

#### Category Management (Admin Only)

```
POST /api/category/create            - Create category
POST /api/category/fetch-all         - Fetch all categories
POST /api/category/fetch-single      - Fetch single category
POST /api/category/update            - Update category
POST /api/category/delete            - Delete category
```

#### Post Management (Role-Based)

```
POST /api/post/create                - Create post (Author+)
POST /api/post/fetch-all             - Fetch posts (based on visibility)
POST /api/post/fetch-single          - Fetch post (based on visibility)
POST /api/post/update                - Update own post (Author+, not Contributor)
POST /api/post/delete                - Delete own post (Author+, not Contributor)
```

#### Note Management (Personal)

```
POST /api/note/create                - Create personal note
POST /api/note/fetch-all             - Fetch own notes (or all if admin)
POST /api/note/fetch-single          - Fetch own note
POST /api/note/update                - Update own note
POST /api/note/delete                - Delete own note
```

#### Tag Management (Admin Only)

```
POST /api/tag/create                 - Create tag
POST /api/tag/fetch-all              - Fetch tags
POST /api/tag/fetch-single           - Fetch single tag
POST /api/tag/update                 - Update tag
POST /api/tag/delete                 - Delete tag
```

#### Post-Tag Association (Author+)

```
POST /api/post-tag/attach            - Attach tag to post (Author+, not Contributor)
POST /api/post-tag/detach            - Detach tag from post (Author+, not Contributor)
```

#### Country Management (Admin Only)

```
POST /api/country/create             - Create country
POST /api/country/fetch-all          - Fetch countries
POST /api/country/fetch-single       - Fetch single country
POST /api/country/update             - Update country
POST /api/country/delete             - Delete country
```

#### Role Management (Admin Only)

```
POST /api/role/create                - Create role
POST /api/role/fetch-all             - Fetch roles
POST /api/role/fetch-single          - Fetch single role
POST /api/role/update                - Update role
POST /api/role/delete                - Delete role
```

---

## Access Control Rules

### Posts (`tbl_post`)

| Role | Create | Read | Update | Delete |
|------|--------|------|--------|--------|
| Admin | All | All | All | All |
| Author | Own | All + private own | Own only | Own only |
| Contributor | ❌ | Public only | ❌ | ❌ |
| Unauthenticated | ❌ | Public only | ❌ | ❌ |

### Notes (`tbl_note`)

| Role | Create | Read | Update | Delete |
|------|--------|------|--------|--------|
| Admin | All | All | All | All |
| Author | Own | Own | Own | Own |
| Contributor | ❌ | ❌ | ❌ | ❌ |

### System Resources (Categories, Tags, Roles, Countries)

| Role | Create | Read | Update | Delete |
|------|--------|------|--------|--------|
| Admin | ✅ | ✅ | ✅ | ✅ |
| Author | ❌ | ✅ | ❌ | ❌ |
| Contributor | ❌ | ✅ | ❌ | ❌ |

### User Management

| Role | Create | Read | Update | Delete |
|------|--------|------|--------|--------|
| Admin | ✅ | All | Own or all | All |
| Author | ❌ | Own | Own | ❌ |
| Contributor | ❌ | Own | Own | ❌ |

---

## Implementation Details

### AuthMiddleware

Located in: `app/Http/Controllers/AuthMiddleware.php`

Key methods:
- `authenticate($request)` - Validates token and returns user info
- `requireAuth($request)` - Returns error if not authenticated
- `requireRole($request, $allowedRoles)` - Checks if user has required role
- `isAdmin($user)` - Check if user is admin
- `isAuthor($user)` - Check if user is author or admin
- `isContributor($user)` - Check if user is contributor
- `ownsResource($user, $resourceOwnerId)` - Check resource ownership

### RoleAuthorizationHelper

Located in: `app/Http/Controllers/RoleAuthorizationHelper.php`

Central utility class for role-based authorization checks:
- Role ID constants and names
- Helper methods for permission checking
- Role descriptions and permission summaries

### Controllers with Authorization

All controllers implement authorization checks:
- `UserModelController` - User management
- `PostModelController` - Post CRUD operations
- `NoteModelController` - Personal notes
- `CategoryModelController` - Category management
- `TagModelController` - Tag management
- `MasterCountryModelController` - Country management
- `MasterRoleModelController` - Role management
- `PostTagModelController` - Post-tag associations

---

## Testing Authorization

### Test Admin Access

```bash
# Login as admin
curl -X POST http://localhost:8000/api/user/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Use token to create category
curl -X POST http://localhost:8000/api/category/create \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"category_name":"Tech"}'
```

### Test Authorization Error

```bash
# Try to access protected endpoint without token
curl -X POST http://localhost:8000/api/category/fetch-all \
  -H "Content-Type: application/json" \
  -d '{}'

# Response: 401 Authorization Error
```

### Test Insufficient Permissions

```bash
# Login as contributor
curl -X POST http://localhost:8000/api/user/login \
  -H "Content-Type: application/json" \
  -d '{"email":"contributor@example.com","password":"password"}'

# Try to create post (contributors can't)
curl -X POST http://localhost:8000/api/post/create \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","content":"..."}'

# Response: 403 Forbidden
```

---

## Security Best Practices

1. **Always use HTTPS** in production for token transmission
2. **Token Expiration**: Consider implementing token expiration in future versions
3. **Token Refresh**: Implement refresh tokens for long-lived sessions
4. **Rate Limiting**: Add rate limiting on login endpoint to prevent brute force
5. **CORS Configuration**: Configure CORS properly in production
6. **Session Handling**: Sessions are automatically invalidated on logout
7. **Password**: Always hash passwords (using Laravel's Hash facade)

---

## Migration to Production

Ensure the following are in place:

1. ✅ `api_token` column exists in `tbl_user` table (migration applied)
2. ✅ All routes require Bearer token authentication (documented in routes/api.php)
3. ✅ HTTPS is enabled for all API endpoints
4. ✅ API rate limiting is configured
5. ✅ CORS headers are properly configured
6. ✅ Database backups are in place

---

## Future Enhancements

1. Token expiration and refresh tokens
2. Permission caching for better performance
3. API key authentication for service-to-service calls
4. Two-factor authentication (2FA)
5. Permission audit logging
6. Role-specific API rate limiting
7. OAuth2 integration

---

## Support & Maintenance

For issues or questions:
- Check controller files in `app/Http/Controllers/`
- Review `AuthMiddleware.php` for authentication logic
- Review `RoleAuthorizationHelper.php` for permission checks
- Check database migrations in `database/migrations/`

Last Updated: May 20, 2026
