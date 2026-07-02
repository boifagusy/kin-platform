<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\SecurityMonitorService;
use Illuminate\Http\Request;

class SecurityMonitorController extends Controller
{
    protected $securityMonitor;

    public function __construct(SecurityMonitorService $securityMonitor)
    {
        $this->securityMonitor = $securityMonitor;
    }

    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->securityMonitor->getMetrics(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
