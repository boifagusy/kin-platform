<?php

namespace App\Services\Admin;

use App\Models\AdminLog;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditService
{
    protected $adminLogsPerPage = 20;

    public function getAuditLogs(array $filters = [], int $perPage = null)
    {
        $perPage = $perPage ?? $this->adminLogsPerPage;
        
        $query = AdminLog::with('admin');
        
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        
        if (!empty($filters['admin_id'])) {
            $query->where('admin_user_id', $filters['admin_id']);
        }
        
        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getStats()
    {
        return [
            'total_logs' => AdminLog::count(),
            'unique_admins' => AdminLog::distinct('admin_user_id')->count('admin_user_id'),
            'action_types' => AdminLog::distinct('action')->count('action'),
            'recent_logs' => AdminLog::orderBy('created_at', 'desc')->limit(10)->get(),
        ];
    }

    public function getAdmins()
    {
        return AdminUser::orderBy('name')->get();
    }

    public function getActionTypes()
    {
        return AdminLog::distinct('action')->pluck('action')->toArray();
    }

    /**
     * Create audit record (internal helper)
     */
    protected function createAuditRecord(string $action, string $entityType, int $entityId, ?array $oldValues = null, ?array $newValues = null): void
    {
        AdminLog::create([
            'admin_user_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log admin creation
     */
    public function logAdminCreated(AdminUser $admin, AdminUser $createdBy): void
    {
        $this->createAuditRecord(
            action: 'ADMIN_CREATED',
            entityType: 'admin',
            entityId: $admin->id,
            newValues: $admin->only(['name', 'email', 'role', 'is_active'])
        );
    }

    /**
     * Log admin update
     */
    public function logAdminUpdated(AdminUser $admin, array $oldValues, array $newValues): void
    {
        $this->createAuditRecord(
            action: 'ADMIN_UPDATED',
            entityType: 'admin',
            entityId: $admin->id,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }

    /**
     * Log admin activation
     */
    public function logAdminActivated(AdminUser $admin): void
    {
        $this->createAuditRecord(
            action: 'ADMIN_ACTIVATED',
            entityType: 'admin',
            entityId: $admin->id,
            newValues: ['is_active' => true]
        );
    }

    /**
     * Log admin deactivation
     */
    public function logAdminDeactivated(AdminUser $admin): void
    {
        $this->createAuditRecord(
            action: 'ADMIN_DEACTIVATED',
            entityType: 'admin',
            entityId: $admin->id,
            newValues: ['is_active' => false]
        );
    }
}
