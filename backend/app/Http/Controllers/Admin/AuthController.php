<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\LogsSecurityEvents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use LogsSecurityEvents;

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->remember)) {
            // Log admin login success
            $this->logSecurityEvent('admin_login_success', 'info', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        // Log admin login failure
        $this->logSecurityEvent('admin_login_failed', 'warning', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if ($user) {
            $this->logSecurityEvent('admin_logout', 'info', [
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
