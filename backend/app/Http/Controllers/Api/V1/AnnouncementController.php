<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $platform = $request->get('platform', 'all');
        $version = $request->get('version');

        $announcements = $this->service->getActive($platform, $version);

        return response()->json([
            'success' => true,
            'data' => $announcements,
            'server_time' => now()->toISOString(),
        ]);
    }
}
