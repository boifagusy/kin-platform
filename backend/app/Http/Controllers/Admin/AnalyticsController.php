<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    public function index(AnalyticsService $analytics)
    {
        return view('admin.analytics.index', [
            'stats' => $analytics->getDashboardStats(),
        ]);
    }

    public function api(AnalyticsService $analytics)
    {
        return response()->json([
            'success' => true,
            'data' => $analytics->getDashboardStats(),
        ]);
    }
}
