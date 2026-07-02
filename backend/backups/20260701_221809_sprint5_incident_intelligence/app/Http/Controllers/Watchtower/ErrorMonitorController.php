<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\ErrorMonitorService;
use Illuminate\Http\Request;

class ErrorMonitorController extends Controller
{
    protected $errorMonitor;

    public function __construct(ErrorMonitorService $errorMonitor)
    {
        $this->errorMonitor = $errorMonitor;
    }

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->errorMonitor->getMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
