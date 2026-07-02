<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WatchtowerIncident;
use Illuminate\Http\Request;

class AdminIncidentController extends Controller
{
    public function index(Request $request)
    {
        $query = WatchtowerIncident::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        $incidents = $query->orderBy('detected_at', 'desc')->get();

        return view('admin.watchtower.incidents', [
            'incidents' => $incidents,
            'stats' => [
                'total' => WatchtowerIncident::count(),
                'critical' => WatchtowerIncident::where('severity', 'critical')->count(),
                'open' => WatchtowerIncident::whereNotIn('status', ['resolved', 'closed'])->count(),
                'resolved' => WatchtowerIncident::where('status', 'resolved')->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $incident = WatchtowerIncident::findOrFail($id);
        return view('admin.watchtower.incident-detail', compact('incident'));
    }
}
