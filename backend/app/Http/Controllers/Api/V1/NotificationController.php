<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $page = (int) $request->get('page', 1);
        return response()->json($this->service->getUnifiedFeed($user->id, $page));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        return response()->json(['unread_count' => $this->service->getUnifiedFeed($user->id)['unread_count']]);
    }

    public function badge(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        return response()->json(['count' => $this->service->getBadgeCount($user->id)]);
    }

    public function markRead(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        $this->service->markRead($id, $user->id);
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
        $this->service->markAllRead($user->id);
        return response()->json(['success' => true]);
    }
}
