<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // OTP-sending endpoints: stricter IP-based limit to stop basic flooding.
        // This is a coarse first layer — the real per-identifier cooldown
        // (otp_resend_cooldown) is enforced inside the service layer itself,
        // since IP throttling alone can't stop someone spamming one phone
        // number from many different IPs.
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });
    }
}
