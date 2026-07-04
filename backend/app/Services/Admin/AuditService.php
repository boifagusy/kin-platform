<?php

namespace App\Services\Admin;

use App\Models\AdminLog;
use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;

class AuditService
{
    public function getLogs(int $perPage = 20, int $page = 1)
    {
        return AdminLog::with('adminUser')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getStats(): array
    {
        $total = AdminLog::count();
        $today = AdminLog::whereDate('created_at', today())->count();
        $thisWeek = AdminLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = AdminLog::whereMonth('created_at', now()->month)->count();

        // Get actions by type
        $actionsByType = AdminLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->pluck('count', 'action')
            ->toArray();

        // Get users with most actions
        $topUsers = AdminLog::with('adminUser')
            ->select('admin_user_id', DB::raw('count(*) as count'))
            ->groupBy('admin_user_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'name' => $log->adminUser->name ?? 'Unknown',
                    'count' => $log->count,
                ];
            })
            ->toArray();

        return [
            'total' => $total,
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'actions_by_type' => $actionsByType,
            'top_users' => $topUsers,
        ];
    }

    public function getDistinctActions(): array
    {
        return AdminLog::distinct('action')->pluck('action')->toArray();
    }

    public function getAdminUsers(): array
    {
        return AdminUser::orderBy('name')->get()->toArray();
    }
}
