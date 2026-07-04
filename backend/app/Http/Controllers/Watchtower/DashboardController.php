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
        // Get incident stats
        $totalIncidents = WatchtowerIncident::count();
        $criticalIncidents = WatchtowerIncident::where('severity', 'critical')->count();
        $openIncidents = WatchtowerIncident::where('status', 'open')->count();
        $resolvedIncidents = WatchtowerIncident::where('status', 'resolved')
            ->where('updated_at', '>=', now()->subHours(24))
            ->count();

        // Get recent incidents
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
}
