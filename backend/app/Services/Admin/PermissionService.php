<?php

namespace App\Services\Admin;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    // Roles
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_OPERATIONS_ADMIN = 'operations_admin';
    const ROLE_SAFETY_ADMIN = 'safety_admin';
    const ROLE_SUPPORT_ADMIN = 'support_admin';
    const ROLE_FINANCE_ADMIN = 'finance_admin';
    const ROLE_MARKETING_ADMIN = 'marketing_admin';
    const ROLE_ANALYTICS_ADMIN = 'analytics_admin';
    const ROLE_AUDITOR = 'auditor';
    const ROLE_VIEWER_ADMIN = 'viewer_admin';

    // Permission Groups
    const PERM_DASHBOARD_VIEW = 'dashboard.view';

    const PERM_USERS_VIEW = 'users.view';
    const PERM_USERS_VIEW_DETAILS = 'users.view_details';
    const PERM_USERS_CREATE = 'users.create';
    const PERM_USERS_UPDATE = 'users.update';
    const PERM_USERS_SUSPEND = 'users.suspend';
    const PERM_USERS_ACTIVATE = 'users.activate';
    const PERM_USERS_DELETE = 'users.delete';

    const PERM_ADMINS_VIEW = 'admins.view';
    const PERM_ADMINS_CREATE = 'admins.create';
    const PERM_ADMINS_UPDATE = 'admins.update';
    const PERM_ADMINS_DELETE = 'admins.delete';
    const PERM_ADMINS_ACTIVATE = 'admins.activate';
    const PERM_ADMINS_ASSIGN_ROLES = 'admins.assign_roles';

    const PERM_AUDIT_VIEW = 'audit.view';
    const PERM_AUDIT_EXPORT = 'audit.export';

    const PERM_SAFETY_WATCHTOWER = 'safety.watchtower';
    const PERM_SAFETY_ALERTS = 'safety.alerts';
    const PERM_SAFETY_RESPOND = 'safety.respond';
    const PERM_SAFETY_BROADCAST = 'safety.broadcast';
    const PERM_SAFETY_HISTORY = 'safety.history';

    const PERM_CAMPAIGNS_VIEW = 'campaigns.view';
    const PERM_CAMPAIGNS_CREATE = 'campaigns.create';
    const PERM_CAMPAIGNS_EDIT = 'campaigns.edit';
    const PERM_CAMPAIGNS_PUBLISH = 'campaigns.publish';
    const PERM_CAMPAIGNS_DELETE = 'campaigns.delete';

    const PERM_ANNOUNCEMENTS_VIEW = 'announcements.view';
    const PERM_ANNOUNCEMENTS_CREATE = 'announcements.create';
    const PERM_ANNOUNCEMENTS_PUBLISH = 'announcements.publish';
    const PERM_ANNOUNCEMENTS_DELETE = 'announcements.delete';

    const PERM_NOTIFICATIONS_SEND = 'notifications.send';
    const PERM_NOTIFICATIONS_TEMPLATES = 'notifications.templates';
    const PERM_NOTIFICATIONS_HISTORY = 'notifications.history';

    const PERM_SETTINGS_VIEW = 'settings.view';
    const PERM_SETTINGS_UPDATE = 'settings.update';
    const PERM_SETTINGS_SECURITY = 'settings.security';
    const PERM_SETTINGS_SYSTEM = 'settings.system';

    const PERM_RELEASE_VIEW = 'release.view';
    const PERM_RELEASE_CREATE = 'release.create';
    const PERM_RELEASE_EDIT = 'release.edit';
    const PERM_RELEASE_SUBMIT = 'release.submit';
    const PERM_RELEASE_APPROVE = 'release.approve';
    const PERM_RELEASE_ACTIVATE = 'release.activate';
    const PERM_RELEASE_ARCHIVE = 'release.archive';
    const PERM_RELEASE_DELETE = 'release.delete';

    const PERM_ANALYTICS_VIEW = 'analytics.view';
    const PERM_ANALYTICS_EXPORT = 'analytics.export';

    const PERM_FINANCE_VIEW = 'finance.view';
    const PERM_FINANCE_REFUNDS = 'finance.refunds';
    const PERM_FINANCE_WALLETS = 'finance.wallets';
    const PERM_FINANCE_REPORTS = 'finance.reports';

    // Role permission mapping
    protected array $rolePermissions = [];

    public function __construct()
    {
        $this->rolePermissions = $this->buildRoleMatrix();
    }

    private function buildRoleMatrix(): array
    {
        $all = $this->allPermissionConstants();

        return [
            self::ROLE_SUPER_ADMIN => $all,

            self::ROLE_OPERATIONS_ADMIN => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_USERS_VIEW, self::PERM_USERS_VIEW_DETAILS, self::PERM_USERS_CREATE,
                self::PERM_USERS_UPDATE, self::PERM_USERS_SUSPEND, self::PERM_USERS_ACTIVATE, self::PERM_USERS_DELETE,
                self::PERM_ADMINS_VIEW, self::PERM_ADMINS_CREATE, self::PERM_ADMINS_UPDATE,
                self::PERM_ADMINS_DELETE, self::PERM_ADMINS_ACTIVATE, self::PERM_ADMINS_ASSIGN_ROLES,
                self::PERM_AUDIT_VIEW, self::PERM_AUDIT_EXPORT,
                self::PERM_SETTINGS_VIEW, self::PERM_SETTINGS_UPDATE, self::PERM_SETTINGS_SECURITY, self::PERM_SETTINGS_SYSTEM,
                self::PERM_RELEASE_VIEW, self::PERM_RELEASE_CREATE, self::PERM_RELEASE_EDIT,
                self::PERM_RELEASE_SUBMIT, self::PERM_RELEASE_APPROVE, self::PERM_RELEASE_ACTIVATE,
                self::PERM_RELEASE_ARCHIVE, self::PERM_RELEASE_DELETE,
            ],

            self::ROLE_SAFETY_ADMIN => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_SAFETY_WATCHTOWER, self::PERM_SAFETY_ALERTS, self::PERM_SAFETY_RESPOND,
                self::PERM_SAFETY_BROADCAST, self::PERM_SAFETY_HISTORY,
            ],

            self::ROLE_SUPPORT_ADMIN => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_USERS_VIEW, self::PERM_USERS_VIEW_DETAILS,
                self::PERM_USERS_SUSPEND, self::PERM_USERS_ACTIVATE,
            ],

            self::ROLE_FINANCE_ADMIN => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_FINANCE_VIEW, self::PERM_FINANCE_REFUNDS, self::PERM_FINANCE_WALLETS, self::PERM_FINANCE_REPORTS,
            ],

            self::ROLE_MARKETING_ADMIN => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_CAMPAIGNS_VIEW, self::PERM_CAMPAIGNS_CREATE, self::PERM_CAMPAIGNS_EDIT,
                self::PERM_CAMPAIGNS_PUBLISH, self::PERM_CAMPAIGNS_DELETE,
                self::PERM_ANNOUNCEMENTS_VIEW, self::PERM_ANNOUNCEMENTS_CREATE,
                self::PERM_ANNOUNCEMENTS_PUBLISH, self::PERM_ANNOUNCEMENTS_DELETE,
                self::PERM_NOTIFICATIONS_SEND, self::PERM_NOTIFICATIONS_TEMPLATES, self::PERM_NOTIFICATIONS_HISTORY,
            ],

            self::ROLE_ANALYTICS_ADMIN => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_ANALYTICS_VIEW, self::PERM_ANALYTICS_EXPORT,
            ],

            self::ROLE_AUDITOR => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_AUDIT_VIEW, self::PERM_AUDIT_EXPORT,
                self::PERM_ANALYTICS_VIEW,
            ],

            self::ROLE_VIEWER_ADMIN => [
                self::PERM_DASHBOARD_VIEW,
                self::PERM_USERS_VIEW,
            ],
        ];
    }

    private function allPermissionConstants(): array
    {
        $reflection = new \ReflectionClass($this);
        return array_values(array_filter($reflection->getConstants(), fn($k) => str_starts_with($k, 'PERM_'), ARRAY_FILTER_USE_KEY));
    }

    // Authorization
    public function getCurrentRole(): ?string
    {
        return Auth::guard('admin')->user()?->role;
    }

    public function hasPermission(string $permission): bool
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return false;
        if ($admin->role === self::ROLE_SUPER_ADMIN) return true;

        $rolePerms = $this->rolePermissions[$admin->role] ?? [];
        if (in_array($permission, $rolePerms)) return true;

        $customPerms = is_string($admin->permissions) ? json_decode($admin->permissions, true) : ($admin->permissions ?? []);
        return in_array($permission, (array) $customPerms);
    }

    // Role & Permission management
    public function getAvailableRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_OPERATIONS_ADMIN => 'Operations Admin',
            self::ROLE_SAFETY_ADMIN => 'Safety Admin',
            self::ROLE_SUPPORT_ADMIN => 'Support Admin',
            self::ROLE_FINANCE_ADMIN => 'Finance Admin',
            self::ROLE_MARKETING_ADMIN => 'Marketing Admin',
            self::ROLE_ANALYTICS_ADMIN => 'Analytics Admin',
            self::ROLE_AUDITOR => 'Auditor',
            self::ROLE_VIEWER_ADMIN => 'Viewer Admin',
        ];
    }

    public function getPermissionGroups(): array
    {
        return [
            'Dashboard' => [self::PERM_DASHBOARD_VIEW],
            'Users' => [self::PERM_USERS_VIEW, self::PERM_USERS_VIEW_DETAILS, self::PERM_USERS_CREATE, self::PERM_USERS_UPDATE, self::PERM_USERS_SUSPEND, self::PERM_USERS_ACTIVATE, self::PERM_USERS_DELETE],
            'Admins' => [self::PERM_ADMINS_VIEW, self::PERM_ADMINS_CREATE, self::PERM_ADMINS_UPDATE, self::PERM_ADMINS_DELETE, self::PERM_ADMINS_ACTIVATE, self::PERM_ADMINS_ASSIGN_ROLES],
            'Audit' => [self::PERM_AUDIT_VIEW, self::PERM_AUDIT_EXPORT],
            'Safety' => [self::PERM_SAFETY_WATCHTOWER, self::PERM_SAFETY_ALERTS, self::PERM_SAFETY_RESPOND, self::PERM_SAFETY_BROADCAST, self::PERM_SAFETY_HISTORY],
            'Campaigns' => [self::PERM_CAMPAIGNS_VIEW, self::PERM_CAMPAIGNS_CREATE, self::PERM_CAMPAIGNS_EDIT, self::PERM_CAMPAIGNS_PUBLISH, self::PERM_CAMPAIGNS_DELETE],
            'Announcements' => [self::PERM_ANNOUNCEMENTS_VIEW, self::PERM_ANNOUNCEMENTS_CREATE, self::PERM_ANNOUNCEMENTS_PUBLISH, self::PERM_ANNOUNCEMENTS_DELETE],
            'Notifications' => [self::PERM_NOTIFICATIONS_SEND, self::PERM_NOTIFICATIONS_TEMPLATES, self::PERM_NOTIFICATIONS_HISTORY],
            'Settings' => [self::PERM_SETTINGS_VIEW, self::PERM_SETTINGS_UPDATE, self::PERM_SETTINGS_SECURITY, self::PERM_SETTINGS_SYSTEM],
            'Version Management' => [self::PERM_RELEASE_VIEW, self::PERM_RELEASE_CREATE, self::PERM_RELEASE_EDIT, self::PERM_RELEASE_SUBMIT, self::PERM_RELEASE_APPROVE, self::PERM_RELEASE_ACTIVATE, self::PERM_RELEASE_ARCHIVE, self::PERM_RELEASE_DELETE],
            'Analytics' => [self::PERM_ANALYTICS_VIEW, self::PERM_ANALYTICS_EXPORT],
            'Finance' => [self::PERM_FINANCE_VIEW, self::PERM_FINANCE_REFUNDS, self::PERM_FINANCE_WALLETS, self::PERM_FINANCE_REPORTS],
        ];
    }

    public function getPermissionsForRole(string $role): array
    {
        return $this->rolePermissions[$role] ?? [];
    }

    public function getEffectivePermissions(AdminUser $admin): array
    {
        $perms = $this->getPermissionsForRole($admin->role);
        $customPerms = is_string($admin->permissions) ? json_decode($admin->permissions, true) : ($admin->permissions ?? []);
        return array_unique(array_merge($perms, (array) $customPerms));
    }

    public function updateAdminPermissions(AdminUser $admin, string $role, array $customPermissions = []): bool
    {
        $admin->role = $role;
        $defaultPerms = $this->getPermissionsForRole($role);
        $extraPerms = array_diff($customPermissions, $defaultPerms);
        $admin->permissions = json_encode(array_values($extraPerms));
        return $admin->save();
    }
}
