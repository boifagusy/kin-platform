<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\SafetyEngineMonitorService;
use Illuminate\Http\Request;

class SafetyEngineMonitorController extends Controller
{
    protected $safetyMonitor;

    public function __construct(SafetyEngineMonitorService $safetyMonitor)
    {
        $this->safetyMonitor = $safetyMonitor;
    }

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->safetyMonitor->getMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
