<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Services\Recovery\RecoveryEngine;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecoveryController extends Controller
{
    protected RecoveryEngine $engine;
    
    public function __construct(RecoveryEngine $engine)
    {
        $this->engine = $engine;
    }
    
    public function dashboard(): View
    {
        $stats = $this->engine->getStats();
        $recent = \App\Models\RecoveryAttempt::with('action')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
            
        return view('recovery.dashboard', [
            'stats' => $stats,
            'recent' => $recent
        ]);
    }
    
    public function runPolicy(Request $request)
    {
        $request->validate([
            'policy' => 'required|string',
            'incident_id' => 'nullable|string',
            'subsystem' => 'nullable|string',
            'trigger' => 'nullable|string'
        ]);
        
        try {
            $attempt = $this->engine->runPolicy(
                $request->input('policy'),
                $request->input('incident_id'),
                $request->input('subsystem'),
                $request->input('trigger')
            );
            
            return response()->json([
                'success' => true,
                'attempt_id' => $attempt->id,
                'status' => $attempt->status,
                'message' => $attempt->message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getStats(Request $request)
    {
        return response()->json($this->engine->getStats());
    }
    
    public function getAttempts(Request $request)
    {
        $limit = $request->input('limit', 20);
        $attempts = \App\Models\RecoveryAttempt::with('action')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
            
        return response()->json(['attempts' => $attempts]);
    }
}
    
    public function dashboardData(): \Illuminate\View\View
    {
        $stats = $this->engine->getStats();
        
        // Recent attempts with history
        $recent = \App\Models\RecoveryAttempt::with(['action', 'history'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
            
        // Active recoveries (running)
        $active = \App\Models\RecoveryAttempt::where('status', 'running')
            ->count();
            
        // Average recovery time
        $avgTime = \App\Models\RecoveryAttempt::where('status', 'success')
            ->whereNotNull('duration_ms')
            ->avg('duration_ms') ?? 0;
            
        // Escalated recoveries
        $escalated = \App\Models\RecoveryAttempt::where('escalated', true)
            ->count();
            
        return view('recovery.dashboard', [
            'stats' => $stats,
            'recent' => $recent,
            'active' => $active,
            'avgTime' => round($avgTime / 1000, 2), // Convert to seconds
            'escalated' => $escalated,
            'lastUpdated' => now()->toIso8601String()
        ]);
    }
    
    public function dashboardData(): \Illuminate\View\View
    {
        $stats = $this->engine->getStats();
        
        // Recent attempts with history
        $recent = \App\Models\RecoveryAttempt::with(['action', 'history'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
            
        // Active recoveries (running)
        $active = \App\Models\RecoveryAttempt::where('status', 'running')
            ->count();
            
        // Average recovery time
        $avgTime = \App\Models\RecoveryAttempt::where('status', 'success')
            ->whereNotNull('duration_ms')
            ->avg('duration_ms') ?? 0;
            
        // Escalated recoveries
        $escalated = \App\Models\RecoveryAttempt::where('escalated', true)
            ->count();
            
        return view('recovery.dashboard', [
            'stats' => $stats,
            'recent' => $recent,
            'active' => $active,
            'avgTime' => round($avgTime / 1000, 2), // Convert to seconds
            'escalated' => $escalated,
            'lastUpdated' => now()->toIso8601String()
        ]);
    }
