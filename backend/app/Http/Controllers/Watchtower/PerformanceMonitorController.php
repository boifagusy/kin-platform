<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\PerformanceMonitorService;
use Illuminate\Http\Request;

class PerformanceMonitorController extends Controller
{
    protected $performanceMonitor;

    public function __construct(PerformanceMonitorService $performanceMonitor)
    {
        $this->performanceMonitor = $performanceMonitor;
    }

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->performanceMonitor->getMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
