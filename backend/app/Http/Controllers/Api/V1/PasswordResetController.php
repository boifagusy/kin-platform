<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use App\Traits\LogsSecurityEvents;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    use LogsSecurityEvents;

    protected $resetService;

    public function __construct(PasswordResetService $resetService)
    {
        $this->resetService = $resetService;
    }

    /**
     * Step 1: Request OTP — accepts either an email or a phone number
     * POST /api/v1/forgot-pin/send-otp
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string', 'min:5'],
        ]);

        $result = $this->resetService->sendResetOtp($request->identifier);

        // Log OTP request
        $this->logSecurityEvent(
            'otp_requested',
            $result['success'] ? 'info' : 'warning',
            [
                'identifier' => $request->identifier,
                'success' => $result['success'] ?? false,
            ]
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Step 2: Verify OTP
     * POST /api/v1/forgot-pin/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string', 'min:5'],
            'otp' => 'required|string',
        ]);

        $result = $this->resetService->verifyOtp($request->identifier, $request->otp);

        // Log OTP verification
        $this->logSecurityEvent(
            $result['success'] ? 'otp_verified' : 'otp_failed',
            $result['success'] ? 'info' : 'warning',
            [
                'identifier' => $request->identifier,
                'success' => $result['success'] ?? false,
                'error' => $result['error'] ?? null,
            ]
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Step 3: Reset PIN using verified OTP
     * POST /api/v1/forgot-pin/reset
     */
    public function resetPin(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string', 'min:5'],
            'otp' => 'required|string',
            'pin' => 'required|string|size:4',
        ]);

        // Log PIN reset attempt
        $this->logSecurityEvent('pin_reset_attempt', 'info', [
            'identifier' => $request->identifier,
        ]);

        $result = $this->resetService->resetPin(
            $request->identifier,
            $request->otp,
            $request->pin
        );

        // Log PIN reset outcome
        $this->logSecurityEvent(
            $result['success'] ? 'pin_reset_success' : 'pin_reset_failed',
            $result['success'] ? 'info' : 'warning',
            [
                'identifier' => $request->identifier,
                'success' => $result['success'] ?? false,
                'error' => $result['error'] ?? null,
            ]
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
