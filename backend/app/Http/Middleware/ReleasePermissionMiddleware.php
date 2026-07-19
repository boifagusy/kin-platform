<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReleasePermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $permissions = $admin->permissions ?? [];

        if (!in_array($permission, $permissions)) {
            return response()->json(['error' => 'Unauthorized. Required permission: ' . $permission], 403);
        }

        return $next($request);
    }
}
