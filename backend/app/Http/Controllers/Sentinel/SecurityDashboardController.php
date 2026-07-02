<?php

namespace App\Http\Controllers\Sentinel;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Services\Sentinel\SecurityService;
use App\Services\Sentinel\SecurityMetricsService;
use App\Services\Sentinel\ThreatIntelligenceService;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    protected $securityService;
    protected $metricsService;
    protected $threatService;

    public function __construct(
        SecurityService $securityService,
        SecurityMetricsService $metricsService,
        ThreatIntelligenceService $threatService
    ) {
        $this->securityService = $securityService;
        $this->metricsService = $metricsService;
        $this->threatService = $threatService;
    }

    public function index()
    {
        $metrics = $this->metricsService->getMetrics();
        $events = $this->securityService->getRecentEvents(20);
        $suspicious = $this->securityService->detectSuspiciousActivity();
        $suspiciousIps = $this->threatService->getSuspiciousIps();

        return view('sentinel.dashboard', compact('metrics', 'events', 'suspicious', 'suspiciousIps'));
    }

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->metricsService->getMetrics(),
        ]);
    }

    public function events(Request $request)
    {
        $query = SecurityEvent::orderBy('created_at', 'desc');

        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        $events = $query->paginate(50);

        return view('sentinel.events', compact('events'));
    }

    public function show($id)
    {
        $event = SecurityEvent::with('user')->findOrFail($id);
        return view('sentinel.event-detail', compact('event'));
    }

    public function resolve($id)
    {
        $event = SecurityEvent::findOrFail($id);
        $event->update([
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Event resolved successfully');
    }

    public function settings()
    {
        $rules = SecurityAlertRule::all();
        return view('sentinel.settings', compact('rules'));
    }

    public function updateRule(Request $request, $id)
    {
        $rule = SecurityAlertRule::findOrFail($id);
        $rule->update($request->validate([
            'is_active' => 'boolean',
            'threshold' => 'integer',
            'time_window' => 'integer',
            'severity' => 'string',
        ]));

        return redirect()->back()->with('success', 'Rule updated successfully');
    }
}
