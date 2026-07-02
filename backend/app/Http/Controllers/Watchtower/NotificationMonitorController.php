<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\NotificationMonitorService;
use Illuminate\Http\Request;

class NotificationMonitorController extends Controller
{
    protected $notificationMonitor;

    public function __construct(NotificationMonitorService $notificationMonitor)
    {
        $this->notificationMonitor = $notificationMonitor;
    }

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationMonitor->getMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
