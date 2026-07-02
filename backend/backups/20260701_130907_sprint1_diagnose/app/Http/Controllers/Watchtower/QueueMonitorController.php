<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\QueueMonitorService;
use Illuminate\Http\Request;

class QueueMonitorController extends Controller
{
    protected $queueMonitor;

    public function __construct(QueueMonitorService $queueMonitor)
    {
        $this->queueMonitor = $queueMonitor;
    }

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->queueMonitor->getMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function stuck()
    {
        return response()->json([
            'success' => true,
            'data' => $this->queueMonitor->checkStuckJobs(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
