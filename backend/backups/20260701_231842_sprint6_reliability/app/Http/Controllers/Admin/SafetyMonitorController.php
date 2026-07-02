<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SafetyMonitorService;
use Illuminate\Http\Request;

class SafetyMonitorController extends Controller
{
    protected $safetyMonitor;

    public function __construct(SafetyMonitorService $safetyMonitor)
    {
        $this->safetyMonitor = $safetyMonitor;
    }

    public function getMetrics(Request $request)
    {
        $metrics = $this->safetyMonitor->getSafetyMetrics();
        return response()->json(['success' => true, 'data' => $metrics]);
    }

    public function getTrendData(Request $request)
    {
        $metrics = $this->safetyMonitor->getSafetyMetrics();
        return response()->json(['success' => true, 'data' => $metrics['safety_score_trend']]);
    }

    public function alertsIndex(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'priority' => $request->get('priority'),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_direction' => $request->get('sort_direction', 'desc'),
        ];
        
        $alerts = $this->safetyMonitor->getAlertsList($filters);
        $stats = $this->safetyMonitor->getSafetyMetrics();
        
        return view('admin.alerts.index', compact('alerts', 'stats', 'filters'));
    }
    
    public function alertsShow($id)
    {
        $alert = $this->safetyMonitor->getAlertDetail($id);
        
        if (!$alert) {
            abort(404, 'Alert not found');
        }
        
        $notes = $this->safetyMonitor->getAlertNotes($id);
        
        return view('admin.alerts.show', compact('alert', 'notes'));
    }
    
    public function assignAlert(Request $request, $id)
    {
        $adminId = auth()->guard('admin')->id();
        
        $success = $this->safetyMonitor->assignAlert($id, $adminId);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => $success]);
        }
        
        return redirect()->back()->with('success', 'Alert assigned successfully');
    }
    
    public function resolveAlert(Request $request, $id)
    {
        $adminId = auth()->guard('admin')->id();
        $note = $request->input('note', 'Alert resolved');
        
        $success = $this->safetyMonitor->resolveAlert($id, $adminId, $note);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => $success]);
        }
        
        return redirect()->route('admin.alerts.index')->with('success', 'Alert resolved successfully');
    }
    
    public function escalateAlert(Request $request, $id)
    {
        $success = $this->safetyMonitor->escalateAlert($id);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => $success]);
        }
        
        return redirect()->back()->with('success', 'Alert escalated successfully');
    }
    
    public function addAlertNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|min:1'
        ]);
        
        $adminId = auth()->guard('admin')->id();
        
        $note = $this->safetyMonitor->addAlertNote($id, $adminId, $request->note);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'note' => $note]);
        }
        
        return redirect()->back()->with('success', 'Note added');
    }
}
