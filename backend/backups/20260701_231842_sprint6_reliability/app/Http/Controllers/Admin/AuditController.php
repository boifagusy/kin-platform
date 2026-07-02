<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminLog::query();

        // Filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user')) {
            $query->where('admin_user_id', $request->user);
        }

        // Get logs with pagination
        $logs = $query->with('adminUser')->orderBy('created_at', 'desc')->paginate(20);

        // Stats
        $stats = [
            'total' => AdminLog::count(),
            'today' => AdminLog::whereDate('created_at', today())->count(),
            'this_week' => AdminLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => AdminLog::whereMonth('created_at', now()->month)->count(),
            'users' => AdminLog::distinct('admin_user_id')->count('admin_user_id'),
            'actions' => AdminLog::distinct('action')->count('action'),
        ];

        // Get distinct actions for filter
        $actions = AdminLog::distinct()->pluck('action')->filter()->values();

        // Get admin users for filter
        $users = AdminUser::orderBy('name')->get();

        return view('admin.audit.index', compact('logs', 'stats', 'actions', 'users'));
    }

    public function export(Request $request)
    {
        // Export logic
        return response()->json(['message' => 'Export not yet implemented'], 501);
    }
}
