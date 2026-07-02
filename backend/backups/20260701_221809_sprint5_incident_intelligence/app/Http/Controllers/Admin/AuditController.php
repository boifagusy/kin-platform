<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'action_type' => $request->get('action_type'),
            'admin_id' => $request->get('admin_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_direction' => $request->get('sort_direction', 'desc'),
        ];

        $logs = $this->auditService->getAuditLogs($filters);
        $stats = $this->auditService->getStats();
        $actionTypes = $this->auditService->getActionTypes();
        $admins = $this->auditService->getAdmins();

        return view('admin.audit.index', compact('logs', 'stats', 'filters', 'actionTypes', 'admins'));
    }

    public function export(Request $request)
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'action_type' => $request->get('action_type'),
        ];

        $logs = $this->auditService->getAuditLogs($filters, 1000);

        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Timestamp', 'Admin', 'Action', 'Entity Type', 'Entity ID', 'Old Values', 'New Values', 'IP Address']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at,
                    $log->admin->name ?? 'System',
                    $log->action,
                    $log->entity_type,
                    $log->entity_id,
                    $log->old_values,
                    $log->new_values,
                    $log->ip_address,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
