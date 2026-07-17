<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;

class PlatformDashboardController extends Controller
{
    public function index(AnalyticsService $analytics)
    {
        return view('admin.platform.index', [
            'stats' => $analytics->getDashboardStats(),
        ]);
    }
}
