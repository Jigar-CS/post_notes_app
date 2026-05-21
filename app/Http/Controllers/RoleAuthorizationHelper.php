<?php
namespace App\Http\Controllers;

class RoleAuthorizationHelper {
    
    const ROLE_ADMIN= 1;
    const ROLE_AUTHOR= 2;
    const ROLE_CONTRIBUTOR= 3;
    
    const ROLE_NAME_ADMIN= 'Admin';
    const ROLE_NAME_AUTHOR= 'Author';
    const ROLE_NAME_CONTRIBUTOR = 'Contributor';
    
    public static function getRoleName($roleId) {
        switch($roleId) {
            case self::ROLE_ADMIN:
                return self::ROLE_NAME_ADMIN;
            case self::ROLE_AUTHOR:
                return self::ROLE_NAME_AUTHOR;
            case self::ROLE_CONTRIBUTOR:
                return self::ROLE_NAME_CONTRIBUTOR;
            default:
                return 'Unknown';
        }
    }

    public static function isAdmin($user) {
        return $user && isset($user['role_id']) && $user['role_id'] === self::ROLE_ADMIN;
    }

    public static function isAuthor($user) {
        return $user && isset($user['role_id']) && ($user['role_id'] === self::ROLE_ADMIN || $user['role_id'] === self::ROLE_AUTHOR);
    }

    public static function isContributor($user) {
        return $user && isset($user['role_id']) && $user['role_id'] === self::ROLE_CONTRIBUTOR;
    }

 
    public static function isOwner($user, $ownerId) {
        return $user && isset($user['user_id']) && $user['user_id'] == $ownerId;
    }

    public static function canManageUsers($user) {
        return self::isAdmin($user);
    }


    public static function canManageCategories($user) {
        return self::isAdmin($user);
    }

  
    public static function canManageTags($user) {
        return self::isAdmin($user);
    }


    public static function canManageCountries($user) {
        return self::isAdmin($user);
    }

  
    public static function canManageRoles($user) {
        return self::isAdmin($user);
    }

  
    public static function canCreatePost($user) {
        return $user && self::isAuthor($user);
    }

    public static function canUpdatePost($user, $postOwnerId = null) {
        if (!$user) return false;
        if (self::isAdmin($user)) return true;
        if (self::isContributor($user)) return false; // Contributors can't update
        if ($postOwnerId !== null) return self::isOwner($user, $postOwnerId);
        return true; // Author can update their own
    }

    public static function canDeletePost($user, $postOwnerId = null) {
        if (!$user) return false;
        if (self::isAdmin($user)) return true;
        if (self::isContributor($user)) return false; // Contributors can't delete
        if ($postOwnerId !== null) return self::isOwner($user, $postOwnerId);
        return true; // Author can delete their own
    }

 
    public static function canViewPost($user, $isPublic, $postOwnerId = null) {
        if (!$user) return $isPublic; // Non-authenticated can only see public
        if (self::isAdmin($user)) return true; // Admin sees all
        if (self::isAuthor($user)) return true; // Author sees all
        // Contributor sees only public posts
        return $isPublic === 1 || $isPublic === true;
    }

 
    public static function canManageNotes($user, $noteOwnerId = null) {
        if (!$user) return false;
        if (self::isAdmin($user)) return true;
        if ($noteOwnerId !== null) return self::isOwner($user, $noteOwnerId);
        return true;
    }

    public static function canManageTags_PostTags($user) {
        return $user && self::isAuthor($user);
    }

    public static function getPermissionDescription($roleId) {
        switch($roleId) {
            case self::ROLE_ADMIN:
                return 'Full system access - can manage all users, content, categories, tags, and configuration.';
            case self::ROLE_AUTHOR:
                return 'Can create and manage posts, create personal notes, and manage tags on content.';
            case self::ROLE_CONTRIBUTOR:
                return 'Read-only access to public posts and notes.';
            default:
                return 'Unknown role.';
        }
    }

    public static function getAllRolesWithDescriptions() {
        return [
            self::ROLE_ADMIN => [
                'id' => self::ROLE_ADMIN,
                'name' => self::ROLE_NAME_ADMIN,
                'description' => self::getPermissionDescription(self::ROLE_ADMIN)
            ],
            self::ROLE_AUTHOR => [
                'id' => self::ROLE_AUTHOR,
                'name' => self::ROLE_NAME_AUTHOR,
                'description' => self::getPermissionDescription(self::ROLE_AUTHOR)
            ],
            self::ROLE_CONTRIBUTOR => [
                'id' => self::ROLE_CONTRIBUTOR,
                'name' => self::ROLE_NAME_CONTRIBUTOR,
                'description' => self::getPermissionDescription(self::ROLE_CONTRIBUTOR)
            ]
        ];
    }
}
