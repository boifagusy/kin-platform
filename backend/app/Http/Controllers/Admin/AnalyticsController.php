<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

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

    public function notifications(AnalyticsService $analytics)
    {
        return response()->json([
            'success' => true,
            'data' => $analytics->getNotificationAnalytics(),
        ]);
    }

    public function notificationTrends(Request $request, AnalyticsService $analytics)
    {
        $period = $request->get('period', 'daily');
        return response()->json([
            'success' => true,
            'data' => $analytics->getNotificationTrends($period),
        ]);
    }

    public function notificationChannels(AnalyticsService $analytics)
    {
        return response()->json([
            'success' => true,
            'data' => $analytics->getChannelBreakdown(),
        ]);
    }

    public function notificationFailures(AnalyticsService $analytics)
    {
        return response()->json([
            'success' => true,
            'data' => $analytics->getFailureSummary(),
        ]);
    }

    public function notificationManage(Request $request, AnalyticsService $analytics)
    {
        $filters = $request->only(['user_id', 'channel', 'status', 'from', 'to', 'per_page']);
        return response()->json([
            'success' => true,
            'data' => $analytics->searchNotifications($filters),
        ]);
    }

    public function notificationShow(int $id, AnalyticsService $analytics)
    {
        $notification = \App\Models\IncidentNotification::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    public function notificationRetry(int $id, AnalyticsService $analytics)
    {
        return response()->json([
            'success' => true,
            'data' => $analytics->retryNotification($id),
        ]);
    }

    public function notificationRetryBulk(Request $request, AnalyticsService $analytics)
    {
        $ids = $request->input('ids', []);
        return response()->json([
            'success' => true,
            'data' => $analytics->retryBulk($ids),
        ]);
    }
}
