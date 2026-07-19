<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VersionController extends Controller
{
    const VALID_PLATFORMS = ['android', 'ios', 'web'];

    public function __construct(private VersionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $clientCode = (int) $request->get('current', 0);
        $platform = strtolower($request->get('platform', 'android'));

        // Validate platform
        if (!in_array($platform, self::VALID_PLATFORMS)) {
            Log::warning('[B3] Invalid platform requested', [
                'platform' => $platform,
                'client_code' => $clientCode,
            ]);
            return response()->json([
                'error' => 'Invalid platform. Supported: ' . implode(', ', self::VALID_PLATFORMS),
            ], 422);
        }

        // Validate version_code
        if ($clientCode < 0) {
            Log::warning('[B3] Invalid version_code', [
                'client_code' => $clientCode,
                'platform' => $platform,
            ]);
            return response()->json([
                'error' => 'Invalid version_code. Must be a positive integer.',
            ], 422);
        }

        Log::info('[B3] Version check requested', [
            'client_code' => $clientCode,
            'platform' => $platform,
        ]);

        return response()->json(
            $this->service->compareVersion($clientCode, $platform)
        );
    }
}
