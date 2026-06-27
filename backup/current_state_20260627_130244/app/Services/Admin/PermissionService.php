<?php

namespace App\Services\Admin;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    // Role definitions
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_SUPPORT_ADMIN = 'support_admin';
    const ROLE_VIEWER_ADMIN = 'viewer_admin';

    // Permission definitions
    const PERM_VIEW_USERS = 'view_users';
    const PERM_VIEW_USER_DETAILS = 'view_user_details';
    const PERM_SUSPEND_USERS = 'suspend_users';
    const PERM_ACTIVATE_USERS = 'activate_users';
    const PERM_VIEW_AUDIT = 'view_audit';
    const PERM_EXPORT_DATA = 'export_data';
    const PERM_CHANGE_SETTINGS = 'change_settings';
    const PERM_VIEW_SAFETY = 'view_safety';
    const PERM_MANAGE_ADMINS = 'manage_admins';

    // Role permission mapping
    protected $rolePermissions = [
        self::ROLE_SUPER_ADMIN => [
            self::PERM_VIEW_USERS,
            self::PERM_VIEW_USER_DETAILS,
            self::PERM_SUSPEND_USERS,
            self::PERM_ACTIVATE_USERS,
            self::PERM_VIEW_AUDIT,
            self::PERM_EXPORT_DATA,
            self::PERM_CHANGE_SETTINGS,
            self::PERM_VIEW_SAFETY,
            self::PERM_MANAGE_ADMINS,
        ],
        self::ROLE_SUPPORT_ADMIN => [
            self::PERM_VIEW_USERS,
            self::PERM_VIEW_USER_DETAILS,
            self::PERM_VIEW_AUDIT,
            self::PERM_EXPORT_DATA,
            self::PERM_VIEW_SAFETY,
        ],
        self::ROLE_VIEWER_ADMIN => [
            self::PERM_VIEW_USERS,
            self::PERM_VIEW_USER_DETAILS,
            self::PERM_VIEW_AUDIT,
            self::PERM_VIEW_SAFETY,
        ],
    ];

    /**
     * Get current admin's role
     */
    public function getCurrentRole(): ?string
    {
        $admin = Auth::guard('admin')->user();
        return $admin ? $admin->role : null;
    }

    /**
     * Check if current admin has permission
     */
    public function hasPermission(string $permission): bool
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return false;
        }
        
        // Super admin has all permissions
        if ($admin->role === self::ROLE_SUPER_ADMIN) {
            return true;
        }
        
        // Check role-based permissions
        $rolePerms = $this->rolePermissions[$admin->role] ?? [];
        if (in_array($permission, $rolePerms)) {
            return true;
        }
        
        // Check custom permissions from permissions column
        $customPerms = is_string($admin->permissions) 
            ? json_decode($admin->permissions, true) 
            : ($admin->permissions ?? []);
        
        return in_array($permission, $customPerms);
    }

    /**
     * Get all available roles for dropdown
     */
    public function getAvailableRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_SUPPORT_ADMIN => 'Support Admin',
            self::ROLE_VIEWER_ADMIN => 'Viewer Admin',
        ];
    }

    /**
     * Get permissions for a role
     */
    public function getPermissionsForRole(string $role): array
    {
        return $this->rolePermissions[$role] ?? [];
    }

    /**
     * Get all available permissions
     */
    public function getAllPermissions(): array
    {
        return [
            self::PERM_VIEW_USERS => 'View Users',
            self::PERM_VIEW_USER_DETAILS => 'View User Details',
            self::PERM_SUSPEND_USERS => 'Suspend Users',
            self::PERM_ACTIVATE_USERS => 'Activate Users',
            self::PERM_VIEW_AUDIT => 'View Audit Logs',
            self::PERM_EXPORT_DATA => 'Export Data',
            self::PERM_CHANGE_SETTINGS => 'Change System Settings',
            self::PERM_VIEW_SAFETY => 'View Safety Monitor',
            self::PERM_MANAGE_ADMINS => 'Manage Admin Accounts',
        ];
    }

    /**
     * Update admin role and permissions
     */
    public function updateAdminPermissions(AdminUser $admin, string $role, array $customPermissions = []): bool
    {
        $admin->role = $role;
        
        // Only store custom permissions that are not already in role defaults
        $defaultPerms = $this->getPermissionsForRole($role);
        $extraPerms = array_diff($customPermissions, $defaultPerms);
        $admin->permissions = json_encode($extraPerms);
        
        return $admin->save();
    }

    /**
     * Get effective permissions for an admin
     */
    public function getEffectivePermissions(AdminUser $admin): array
    {
        $perms = $this->getPermissionsForRole($admin->role);
        
        $customPerms = is_string($admin->permissions) 
            ? json_decode($admin->permissions, true) 
            : ($admin->permissions ?? []);
        
        return array_unique(array_merge($perms, $customPerms));
    }
}
