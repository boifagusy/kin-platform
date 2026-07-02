<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin) {
            return back()->with('status', 'If that email exists, an OTP has been sent.');
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in password_resets table
        DB::table('admin_password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => Hash::make($otp),
                'token' => null,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(10),
                'used' => false,
            ]
        );

        // Send OTP via email
        try {
            Mail::raw("Your KIN Admin password reset OTP is: $otp\n\nThis OTP expires in 10 minutes.", function ($message) use ($admin) {
                $message->to($admin->email)
                    ->subject('KIN Admin - Password Reset OTP');
            });

            return redirect()->route('admin.password.otp.form', ['email' => $request->email]);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again.']);
        }
    }

    public function showOtpForm(Request $request)
    {
        $email = $request->query('email');
        return view('admin.auth.verify-otp-simple', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $reset = DB::table('admin_password_resets')
            ->where('email', $request->email)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->first();

        if (!$reset || !Hash::check($request->otp, $reset->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // Mark OTP as used
        DB::table('admin_password_resets')
            ->where('email', $request->email)
            ->update(['used' => true]);

        // Store email in session for password reset
        session(['admin_reset_email' => $request->email]);

        return redirect()->route('admin.password.reset.form');
    }

    public function showResetForm()
    {
        $email = session('admin_reset_email');

        if (!$email) {
            return redirect()->route('admin.password.request');
        }

        return view('admin.auth.reset-password', compact('email'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $email = session('admin_reset_email');

        if (!$email || $email !== $request->email) {
            return redirect()->route('admin.password.request')->withErrors(['email' => 'Session expired. Please request a new OTP.']);
        }

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update password
        $admin->password = Hash::make($request->password);
        $admin->save();

        // Clear session
        session()->forget('admin_reset_email');

        // Delete used reset record
        DB::table('admin_password_resets')->where('email', $request->email)->delete();

        return redirect()->route('admin.login')->with('status', 'Password reset successfully. Please login with your new password.');
    }
}
