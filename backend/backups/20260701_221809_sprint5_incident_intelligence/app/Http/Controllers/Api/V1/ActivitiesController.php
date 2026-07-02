<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivitiesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activities = ActivityLog::where('user_id', $user->id)
            ->orderBy('occurred_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'message' => $activity->details,
                    'time_ago' => $activity->occurred_at->diffForHumans(),
                    'timestamp' => $activity->occurred_at->toISOString(),
                ];
            });

        return ApiResponse::success([
            'activities' => $activities,
            'total' => $activities->count(),
        ], 'Activities retrieved successfully');
    }
}
