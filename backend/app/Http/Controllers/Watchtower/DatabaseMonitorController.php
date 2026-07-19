<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\DatabaseMonitorService;
use Illuminate\Http\Request;

class DatabaseMonitorController extends Controller
{
    public function __construct(private DatabaseMonitorService $dbMonitor) {}

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->dbMonitor->getMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function locks()
    {
        return response()->json([
            'success' => true,
            'data' => $this->dbMonitor->checkLocks(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
