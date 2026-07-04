<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Models\WatchtowerIncident;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function overview(): View
    {
        $totalIncidents = WatchtowerIncident::count();
        $criticalIncidents = WatchtowerIncident::where('severity', 'critical')->count();
        $openIncidents = WatchtowerIncident::where('status', 'open')->count();
        $resolvedIncidents = WatchtowerIncident::where('status', 'resolved')
            ->where('updated_at', '>=', now()->subHours(24))
            ->count();

        $recentIncidents = WatchtowerIncident::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.watchtower.overview', [
            'total_incidents' => $totalIncidents,
            'critical_incidents' => $criticalIncidents,
            'open_incidents' => $openIncidents,
            'resolved_incidents' => $resolvedIncidents,
            'recent_incidents' => $recentIncidents,
        ]);
    }

    public function incidents(Request $request)
    {
        $incidents = WatchtowerIncident::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.watchtower.incidents', ['incidents' => $incidents]);
    }

    public function alertRules(Request $request)
    {
        return view('admin.watchtower.alert-rules');
    }

    public function getIncidents()
    {
        $total = WatchtowerIncident::count();
        $critical = WatchtowerIncident::where('severity', 'critical')->count();
        $open = WatchtowerIncident::where('status', 'open')->count();
        $resolved = WatchtowerIncident::where('status', 'resolved')
            ->where('updated_at', '>=', now()->subHours(24))
            ->count();

        $incidents = WatchtowerIncident::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'total' => $total,
            'critical' => $critical,
            'open' => $open,
            'resolved' => $resolved,
            'incidents' => $incidents,
        ]);
    }

    /**
     * Display the system health page
     */
    public function health(): View
    {
        // TEST: Return a simple view to verify routing works
        return view('admin.watchtower.health', [
            'healthScore' => 95,
            'statusColor' => 'green',
            'statusText' => 'Healthy',
            'diskUsage' => ['used' => '2.3 GB', 'free' => '2.2 GB', 'total' => '4.5 GB', 'used_percentage' => 51],
            'database' => ['status' => 'healthy', 'connection' => 'sqlite'],
            'cache' => ['status' => 'healthy', 'driver' => 'file'],
            'storage' => ['status' => 'healthy'],
            'queue' => ['status' => 'healthy', 'connection' => 'database'],
            'memory' => ['used_percentage' => 45, 'current' => '128 MB', 'limit' => '256 MB'],
            'cpu' => ['load_1min' => 0.5, 'load_5min' => 0.6, 'load_15min' => 0.7, 'cpus' => 4],
            'uptime' => '2d 4h 32m',
        ]);
    }
}
