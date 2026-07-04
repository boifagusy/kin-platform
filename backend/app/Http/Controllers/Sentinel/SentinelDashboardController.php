<?php

namespace App\Http\Controllers\Sentinel;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SentinelDashboardController extends Controller
{
    public function dashboard(): View
    {
        // Get security event stats
        $totalEvents = SecurityEvent::count();
        $criticalEvents = SecurityEvent::where('severity', 'critical')->count();
        $highEvents = SecurityEvent::where('severity', 'high')->count();
        $recentEvents = SecurityEvent::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get high risk users
        $highRiskUsers = User::where('is_locked', 1)->count();
        $totalUsers = User::count();

        return view('sentinel.dashboard', [
            'total_events' => $totalEvents,
            'critical_events' => $criticalEvents,
            'high_events' => $highEvents,
            'recent_events' => $recentEvents,
            'high_risk_users' => $highRiskUsers,
            'total_users' => $totalUsers,
        ]);
    }

    public function metrics(Request $request)
    {
        $events = SecurityEvent::selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return response()->json([
            'metrics' => $events,
            'total' => SecurityEvent::count(),
            'critical' => SecurityEvent::where('severity', 'critical')->count(),
        ]);
    }

    public function threats(Request $request)
    {
        $threats = SecurityEvent::where('severity', 'critical')
            ->orWhere('severity', 'high')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('sentinel.threats', ['threats' => $threats]);
    }

    public function highRiskUsers(Request $request)
    {
        $users = User::where('is_locked', 1)->get();
        return response()->json(['users' => $users]);
    }

    public function timeline(Request $request)
    {
        $events = SecurityEvent::with('user')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json(['timeline' => $events]);
    }

    public function charts(Request $request)
    {
        $eventsByType = SecurityEvent::select('event_type', \DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->get();

        return response()->json(['charts' => $eventsByType]);
    }
}
