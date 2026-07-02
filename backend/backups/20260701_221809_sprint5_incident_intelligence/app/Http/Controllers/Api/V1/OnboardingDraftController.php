<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class OnboardingDraftController extends Controller
{
    public function get(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            return response()->json(['success' => true, 'data' => ['step' => $user->onboarding_step, 'draft' => $user->onboarding_draft]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            $validated = $request->validate(['step' => 'nullable|string|max:50', 'draft' => 'nullable|array']);
            $user->onboarding_step = $validated['step'] ?? null;
            $user->onboarding_draft = $validated['draft'] ?? null;
            $user->save();
            return response()->json(['success' => true, 'message' => 'Saved', 'data' => ['step' => $user->onboarding_step, 'draft' => $user->onboarding_draft]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
