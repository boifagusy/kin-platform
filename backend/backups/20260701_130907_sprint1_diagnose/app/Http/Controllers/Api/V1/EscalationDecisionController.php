<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EscalationDecisionEngine;
use App\Services\SafetyScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EscalationDecisionController extends Controller
{
    protected $decisionEngine;
    protected $safetyScoreService;

    public function __construct(
        EscalationDecisionEngine $decisionEngine,
        SafetyScoreService $safetyScoreService
    ) {
        $this->decisionEngine = $decisionEngine;
        $this->safetyScoreService = $safetyScoreService;
    }

    /**
     * Get decision for current user
     */
    public function decide(Request $request)
    {
        $user = $request->user();
        
        $result = $this->decisionEngine->decide($user);
        
        return response()->json([
            'success' => true,
            'data' => $result->toArray(),
        ]);
    }

    /**
     * Get current safety status with decision
     */
    public function status(Request $request)
    {
        $user = $request->user();
        
        $confidence = $this->safetyScoreService->getForUser($user);
        $tier = $this->safetyScoreService->getTier($confidence);
        $decision = $this->decisionEngine->decide($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'confidence' => $confidence,
                'tier' => $tier,
                'tier_color' => $this->safetyScoreService->getTierColor($tier),
                'decision' => $decision->toArray(),
            ],
        ]);
    }

    /**
     * Reset cooldown (admin only)
     */
    public function resetCooldown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($request->user_id);
        $this->decisionEngine->resetCooldown($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Cooldown reset successfully',
        ]);
    }

    /**
     * Get decision history (admin only)
     */
    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'limit' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($request->user_id);
        $limit = $request->limit ?? 10;
        
        $history = $this->decisionEngine->getDecisionHistory($user, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
