<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\DashboardSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private DashboardSnapshotService $snapshotService;

    public function __construct(DashboardSnapshotService $snapshotService)
    {
        $this->snapshotService = $snapshotService;
    }

    public function index(Request $request)
    {
        // FIRST: Try to get user from authenticated token
        $user = Auth::user();

        // SECOND: If not authenticated, try phone parameter
        if (!$user && $request->has('phone')) {
            $phone = $request->input('phone');
            // Clean the phone number
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // If it starts with + and 234, keep as is
            if (strpos($phone, '+234') === 0) {
                // Already correct format
            } elseif (strlen(preg_replace('/[^0-9]/', '', $phone)) === 10) {
                $phone = '+234' . preg_replace('/[^0-9]/', '', $phone);
            } elseif (strlen(preg_replace('/[^0-9]/', '', $phone)) === 11) {
                $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
            }
            
            $user = User::where('phone', $phone)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $snapshot = $this->snapshotService->getSnapshot($user);

        return response()->json([
            'success' => true,
            'data' => $snapshot,
            'message' => 'Dashboard loaded successfully'
        ]);
    }

    public function activities(Request $request)
    {
        // FIRST: Try to get user from authenticated token
        $user = Auth::user();

        // SECOND: If not authenticated, try phone parameter
        if (!$user && $request->has('phone')) {
            $phone = $request->input('phone');
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            if (strpos($phone, '+234') === 0) {
                // Already correct format
            } elseif (strlen(preg_replace('/[^0-9]/', '', $phone)) === 10) {
                $phone = '+234' . preg_replace('/[^0-9]/', '', $phone);
            } elseif (strlen(preg_replace('/[^0-9]/', '', $phone)) === 11) {
                $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
            }
            $user = User::where('phone', $phone)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $page = $request->get('page', 1);
        $perPage = 20;

        $activities = ActivityLog::where('user_id', $user->id)
            ->orderBy('occurred_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'message' => $activity->details,
                    'time_ago' => $activity->occurred_at->diffForHumans(),
                    'timestamp' => $activity->occurred_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activities,
                'page' => $page,
                'has_more' => $activities->count() === $perPage,
            ],
            'message' => 'Activities retrieved successfully'
        ]);
    }
}
