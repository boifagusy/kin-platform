<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\NotificationDispatched;
use App\Models\IncidentNotification;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function getNotifications(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $preferences = UserPreference::getPreferences($userId);

        return response()->json($preferences);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'channels' => 'array',
            'channels.sms' => 'boolean',
            'channels.email' => 'boolean',
            'channels.whatsapp' => 'boolean',
            'channels.push' => 'boolean',
            'categories' => 'array',
            'categories.security' => 'boolean',
            'categories.marketing' => 'boolean',
            'categories.system' => 'boolean',
        ]);

        UserPreference::setPreferences($userId, $validated);

        // Broadcast preference update via N3 realtime infrastructure
        $notification = IncidentNotification::create([
            'user_id' => $userId,
            'type' => 'system',
            'title' => 'Preferences Updated',
            'body' => 'Your notification preferences have been updated.',
            'category' => 'system',
        ]);

        event(new NotificationDispatched($notification));

        return response()->json(['success' => true]);
    }
}
