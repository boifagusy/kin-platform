<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use AppTraitsLogsSecurityEvents;
use AppTraitsLogsSecurityEvents;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
    use LogsSecurityEvents;
    use LogsSecurityEvents;
{
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

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Step 3: Reset PIN using verified OTP
     * POST /api/v1/forgot-pin/reset
     */
    public function resetPin(Request $request)
// Log PIN reset attempt        $this->logSecurityEvent('pin_reset_attempt', 'info', [            'identifier' => $request->identifier,        ]);
    {
        $request->validate([
            'identifier' => ['required', 'string', 'min:5'],
            'otp' => 'required|string',
            'pin' => 'required|string|size:4',
        ]);

        $result = $this->resetService->resetPin(
            $request->identifier,
            $request->otp,
            $request->pin
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
