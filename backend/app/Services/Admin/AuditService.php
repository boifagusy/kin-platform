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
            'today' => AdminLog::whereDate('created_at', today())->count(),
            'this_week' => AdminLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'unique_admins' => AdminLog::distinct('admin_user_id')->count('admin_user_id'),
            'actions_by_type' => AdminLog::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderByDesc('count')
                ->get(),
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

    public function logAdminCreated(AdminUser $admin, AdminUser $createdBy): void
    {
        $this->createAuditRecord(
            action: 'ADMIN_CREATED',
            entityType: 'admin',
            entityId: $admin->id,
            newValues: $admin->only(['name', 'email', 'role', 'is_active'])
        );
    }

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

    public function logAdminActivated(AdminUser $admin): void
    {
        $this->createAuditRecord(
            action: 'ADMIN_ACTIVATED',
            entityType: 'admin',
            entityId: $admin->id,
            newValues: ['is_active' => true]
        );
    }

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
