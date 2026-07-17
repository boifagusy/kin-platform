<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function __construct(private VersionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $clientCode = (int) $request->get('current', 0);
        $platform = $request->get('platform', 'android');

        return response()->json(
            $this->service->compareVersion($clientCode, $platform)
        );
    }
}
