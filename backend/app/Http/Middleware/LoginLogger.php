<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SecurityEvent;
use Illuminate\Http\Request;

class LoginLogger
{
    public function handle(Request $request, Closure $next)
    {
        // Log the login attempt before processing
        $phone = $request->input('phone');
        $ip = $request->ip() ?? 'unknown';
        
        try {
            $event = new SecurityEvent();
            $event->event_type = 'login_attempt';
            $event->severity = 'info';
            $event->source_ip = $ip;
            $event->user_agent = $request->userAgent() ?? 'unknown';
            $event->details = ['phone' => $phone];
            $event->save();
        } catch (\Exception $e) {
            // Silently fail
        }

        return $next($request);
    }
}
