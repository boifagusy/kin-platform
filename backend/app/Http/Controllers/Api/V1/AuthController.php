<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use App\Actions\Auth\ConfirmPhoneAction;
use App\Actions\Auth\CreatePinAction;
use App\Actions\Auth\LoginPinAction;
use App\Actions\Auth\SaveUserDetailsAction;
use App\Actions\Auth\SaveTrustedContactAction;
use App\Actions\Auth\CompleteOnboardingAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\ApiResponse;
use App\Models\ActivityLog;
use App\Models\SosEvent;
use App\Models\SecurityEvent;
use App\Models\EmergencyEscalation;
use App\Services\Sentinel\SecurityService;
use App\Traits\LogsSecurityEvents;

class AuthController extends Controller
{
    use LogsSecurityEvents;

    public function login(Request $request)
    {
        $phone = $request->input('phone');

        // Log the login attempt
        $this->logSecurityEvent('login_attempt', 'info', ['phone' => $phone]);

        if (empty($phone) || !preg_match('/^\+234\d{10}$/', $phone)) {
            return response()->json(['error' => 'Invalid phone number'], 422);
        }
        $user = User::firstOrCreate(['phone' => $phone], [
            'name' => 'Kin User',
            'email' => 'user_' . time() . '@kin.local'
        ]);

        // Per-phone cooldown — prevents one number being spammed for OTPs
        // regardless of which IP the requests come from. throttle:otp on
        // the route handles basic IP-based flooding; this closes the gap.
        $cooldownSeconds = (int) SystemSetting::getValue('otp_resend_cooldown', 60);
        $existing = PasswordReset::where('phone', $phone)->first();

        if ($existing) {
            $secondsSinceLastSend = now()->diffInSeconds($existing->updated_at);

            if ($secondsSinceLastSend < $cooldownSeconds) {
                $wait = $cooldownSeconds - $secondsSinceLastSend;

                \Illuminate\Support\Facades\Log::info('AuthController::login cooldown active, request ignored', [
                    'phone' => $phone,
                    'seconds_remaining' => $wait,
                ]);

                return response()->json([
                    'message' => 'Code sent',
                    'user_id' => $user->id,
                    'cooldown_remaining' => $wait,
                ], 200);
            }
        }

        $length = (int) SystemSetting::getValue('otp_code_length', 6);
        $expiryMinutes = (int) SystemSetting::getValue('otp_expiry_minutes', 10);
        $max = (10 ** $length) - 1;
        $otp = str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);

        PasswordReset::updateOrCreate(
            ['phone' => $phone],
            [
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes($expiryMinutes),
                'used' => false,
            ]
        );

        app(NotificationService::class)->sendOtp($phone, $otp);

        return response()->json(['message' => 'Code sent', 'user_id' => $user->id], 200);
    }

    public function confirmPhone(Request $request, ConfirmPhoneAction $action)
    {
        $result = $action->execute($request->input('phone'));
        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }
        return response()->json($result);
    }

    public function createPin(Request $request, CreatePinAction $action)
    {
        $result = $action->execute($request->input('phone'), $request->input('pin'));
        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }
        return response()->json($result);
    }

    public function loginPin(Request $request, LoginPinAction $action)
    {
        $phone = $request->input('phone');
        $pin = $request->input('pin');

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            // Log failed login - user not found
            $this->logSecurityEvent('login_pin_failed', 'warning', [
                'phone' => $phone,
                'reason' => 'user_not_found'
            ]);
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if pin matches duress PIN first (silent emergency)
        $isDuressPin = !empty($user->duress_pin_hash) && Hash::check($pin, $user->duress_pin_hash);

        if ($isDuressPin) {
            // Log duress PIN used
            $this->logSecurityEvent('duress_pin_used', 'critical', [
                'user_id' => $user->id,
                'phone' => $user->phone,
            ]);

            // Trigger silent SOS - user appears to login normally
            $this->triggerDuressSos($user);

            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'onboarding_completed' => $user->onboarding_completed,
                'is_duress' => false,
            ], 200);
        }

        $result = $action->execute($phone, $pin);

        if (!$result['success']) {
            // Log PIN verification failed
            $this->logSecurityEvent('login_pin_failed', 'warning', [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'reason' => $result['error'] ?? 'invalid_pin'
            ]);
            return response()->json(['error' => $result['error']], 422);
        }

        // Log successful PIN login
        $this->logSecurityEvent('login_pin_success', 'info', [
            'user_id' => $user->id,
            'phone' => $user->phone,
        ]);

        return response()->json($result);
    }

    private function triggerDuressSos(User $user)
    {
        SosEvent::create([
            'user_id' => $user->id,
            'latitude' => null,
            'longitude' => null,
            'triggered_at' => now(),
            'is_duress' => true,
        ]);

        EmergencyEscalation::create([
            'user_id' => $user->id,
            'escalation_type' => 'duress_pin',
            'status' => 'active',
            'priority' => 'critical',
            'location_lat' => null,
            'location_lng' => null,
            'notes' => 'Automatic escalation triggered by duress PIN.',
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'DURESS_PIN_USED',
            'status' => 'active',
            'details' => 'Duress PIN was used to login - SILENT SOS TRIGGERED',
            'occurred_at' => now(),
        ]);

        \Illuminate\Support\Facades\Log::alert('DURESS SOS TRIGGERED', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'name' => $user->name,
            'message' => 'User logged in with duress PIN - Immediate attention required!',
        ]);
    }

    public function saveTrustedContact(Request $request, SaveTrustedContactAction $action)
    {
        $result = $action->execute(
            $request->input('phone'),
            $request->input('contact_name'),
            $request->input('contact_phone'),
            $request->input('invite_sent', false)
        );
        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }
        return response()->json($result);
    }

    public function completeOnboarding(Request $request, CompleteOnboardingAction $action)
    {
        $result = $action->execute(
            $request->input('phone'),
            $request->input('check_in_time'),
            (int) $request->input('grace_period_minutes', 15),
            $request->input('trusted_contact')
        );
        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }
        return response()->json($result);
    }

    public function userDetails(\Illuminate\Http\Request $request, \App\Actions\Auth\SaveUserDetailsAction $action)
    {
        try {
            $result = $action->execute(
                $request->input('phone'),
                $request->input('full_name'),
                $request->input('email')
            );

            if (!$result['success']) {
                return response()->json(['error' => $result['error']], 422);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            $this->logSecurityEvent('logout', 'info', [
                'user_id' => $user->id,
                'phone' => $user->phone,
            ]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}
