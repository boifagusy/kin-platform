<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRoleMiddleware
{
    /**
     * Handle an incoming request.
     * Checks if admin has required role.
     *
     * @param string $role super_admin|safety_admin|support_admin|read_only
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admin has all access
        if ($admin->role === 'super_admin') {
            return $next($request);
        }

        // Check specific role
        if ($admin->role !== $role) {
            abort(403, 'Unauthorized access. Required role: ' . $role);
        }

        return $next($request);
    }
}
