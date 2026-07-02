<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceTrustService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTrustController extends Controller
{
    protected $deviceTrustService;

    public function __construct(DeviceTrustService $deviceTrustService)
    {
        $this->deviceTrustService = $deviceTrustService;
    }

    /**
     * Get device trust status
     */
    public function status(Request $request)
    {
        $user = $request->user();
        
        $breakdown = $this->deviceTrustService->getBreakdown($user);
        
        return response()->json([
            'success' => true,
            'data' => $breakdown,
        ]);
    }

    /**
     * Update device trust
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'model' => 'string|nullable',
            'manufacturer' => 'string|nullable',
            'sdk_version' => 'string|nullable',
            'root_detected' => 'boolean',
            'emulator_detected' => 'boolean',
            'sim_changed' => 'boolean',
            'app_reinstalled' => 'boolean',
            'installation_days' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $deviceTrust = $this->deviceTrustService->updateDeviceTrust($user, $request->all());

        return response()->json([
            'success' => true,
            'data' => [
                'trust_score' => $deviceTrust->trust_score,
                'is_trusted' => $deviceTrust->isTrusted(),
                'trust_level' => $deviceTrust->getTrustLevel(),
                'reasons' => $deviceTrust->reasons,
            ],
        ]);
    }

    /**
     * Verify device
     */
    public function verify(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'fingerprint' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if fingerprint matches
        $deviceTrust = $this->deviceTrustService->getDeviceTrust($user);
        $isVerified = $deviceTrust && $deviceTrust->device_fingerprint === $request->fingerprint;

        return response()->json([
            'success' => true,
            'verified' => $isVerified,
        ]);
    }
}
