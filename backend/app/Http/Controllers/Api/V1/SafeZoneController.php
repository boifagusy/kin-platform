<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSafeZoneRequest;
use App\Http\Requests\UpdateSafeZoneRequest;
use App\Presenters\SafeZonePresenter;
use App\Services\SafeZoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SafeZoneController extends Controller
{
    public function __construct(
        private SafeZoneService $service,
        private SafeZonePresenter $presenter
    ) {}

    public function index(Request $request): JsonResponse
    {
        $zones = $this->service->listForUser($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'zones' => $this->presenter->collection($zones),
                'count' => $zones->count(),
                'active_zone' => $zones->firstWhere('is_default', true)?->name,
            ],
        ]);
    }

    public function store(StoreSafeZoneRequest $request): JsonResponse
    {
        $zone = $this->service->create($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $this->presenter->present($zone),
            'message' => 'Safe zone created',
        ], 201);
    }

    public function update(UpdateSafeZoneRequest $request, int $id): JsonResponse
    {
        $zone = $this->service->findForUser($request->user(), $id);
        $zone = $this->service->update($zone, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $this->presenter->present($zone),
            'message' => 'Safe zone updated',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $zone = $this->service->findForUser($request->user(), $id);
        $this->service->delete($zone);

        return response()->json([
            'success' => true,
            'message' => 'Safe zone deleted',
        ]);
    }
}
