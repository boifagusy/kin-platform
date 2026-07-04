<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request): View
    {
        // Get audit logs
        $logs = $this->auditService->getLogs(
            $request->input('per_page', 20),
            $request->input('page', 1)
        );

        // Get complete stats
        $stats = $this->auditService->getStats();

        // Get distinct actions for filter
        $actions = $this->auditService->getDistinctActions();

        // Get admin users for filter
        $admins = $this->auditService->getAdminUsers();

        return view('admin.audit.index', [
            'logs' => $logs,
            'stats' => $stats,
            'actions' => $actions,
            'admins' => $admins,
        ]);
    }

    public function export(Request $request)
    {
        // Export logic here
        return response()->json(['message' => 'Export not implemented yet']);
    }
}
