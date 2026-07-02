<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SosEvent;
use App\Models\EmergencyEscalation;
use App\Services\Admin\SafetyMonitorService;

class DashboardController extends Controller
{
    protected $safetyMonitor;

    public function __construct(SafetyMonitorService $safetyMonitor)
    {
        $this->safetyMonitor = $safetyMonitor;
    }

    public function index()
    {
        // Total Users
        $totalUsers = User::count();
        
        // Active SOS (where resolved_at is NULL)
        $activeSos = SosEvent::whereNull('resolved_at')->count();
        
        // Active Alerts (emergency escalations with status = active)
        $activeAlerts = EmergencyEscalation::where('status', 'active')->count();
        
        // Tracked Devices (users who have at least one check-in)
        $trackedDevices = User::whereHas('checkIns')->count();
        
        // Business Accounts (placeholder - to be replaced with actual business logic)
        $businessAccounts = 0;
        
        // Recent alerts
        $recentAlerts = SosEvent::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Safety metrics from service
        $safetyMetrics = $this->safetyMonitor->getSafetyMetrics();
        
        return view('admin.dashboard.index', compact(
            'totalUsers',
            'activeSos',
            'activeAlerts',
            'trackedDevices',
            'businessAccounts',
            'recentAlerts',
            'safetyMetrics'
        ));
    }
}
