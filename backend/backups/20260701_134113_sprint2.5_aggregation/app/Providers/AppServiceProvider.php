<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Blade directive for role checking
        Blade::if('adminrole', function ($role) {
            $admin = Auth::guard('admin')->user();
            return $admin && $admin->role === $role;
        });
    }
}
