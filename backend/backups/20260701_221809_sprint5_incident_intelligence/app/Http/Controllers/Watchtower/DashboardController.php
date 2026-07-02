<?php

namespace App\Http\Controllers\Watchtower;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\DashboardAggregationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $aggregationService;

    public function __construct(DashboardAggregationService $aggregationService)
    {
        $this->aggregationService = $aggregationService;
    }

    /**
     * Get aggregated Watchtower dashboard data
     */
    public function index(Request $request)
    {
        try {
            $data = $this->aggregationService->getDashboardData();

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Get live dashboard data (bypass cache)
     */
    public function live(Request $request)
    {
        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('watchtower_dashboard_data');

        return $this->index($request);
    }
}
