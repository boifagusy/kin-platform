<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPreferenceRequest;
use App\Services\UserPreferenceService;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    protected UserPreferenceService $preferenceService;

    public function __construct(UserPreferenceService $preferenceService)
    {
        $this->preferenceService = $preferenceService;
        // Middleware should be in routes, not here
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $preferences = $this->preferenceService->getAll($user);
        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    public function category(Request $request, string $category)
    {
        $validCategories = ['safety', 'notifications', 'privacy', 'appearance', 'account'];
        if (!in_array($category, $validCategories)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid category',
            ], 422);
        }

        $user = $request->user();
        $preferences = $this->preferenceService->getAllForCategory($user, $category);
        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    public function update(UpdateUserPreferenceRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $this->preferenceService->setMany(
            $user,
            $validated['category'],
            $validated['preferences']
        );

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => $this->preferenceService->getAll($user),
        ]);
    }

    public function reset(Request $request, string $category, string $key)
    {
        $user = $request->user();
        $this->preferenceService->reset($user, $category, $key);
        return response()->json([
            'success' => true,
            'message' => 'Preference reset to default',
            'data' => $this->preferenceService->get($user, $category, $key),
        ]);
    }

    public function resetCategory(Request $request, string $category)
    {
        $user = $request->user();
        $this->preferenceService->resetCategory($user, $category);
        return response()->json([
            'success' => true,
            'message' => 'All preferences in category reset to defaults',
            'data' => $this->preferenceService->getAllForCategory($user, $category),
        ]);
    }
}
