<?php

namespace App\Http\Controllers;

use App\Services\Watchtower\HealthService;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    protected $healthService;

    public function __construct(HealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    /**
     * Get system health status
     */
    public function index(Request $request)
    {
        $health = $this->healthService->getHealthStatus();

        return response()->json([
            'success' => true,
            'data' => $health,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get health status (simple version)
     */
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
