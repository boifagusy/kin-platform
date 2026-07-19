<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\ApiMonitorService;
use Illuminate\Http\Request;

class ApiMonitorController extends Controller
{
    public function __construct(private ApiMonitorService $apiMonitor) {}

    public function index()
    {
        return response()->json(['status' => 'ok']);
    }

    public function metrics()
    {
        return response()->json(['metrics' => $this->apiMonitor->getMetrics()]);
    }

    public function degradation()
    {
        return response()->json(['degradation' => $this->apiMonitor->detectDegradation()]);
    }
}
