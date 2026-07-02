<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Sentinel\SecurityService;
use Illuminate\Http\Request;

class SecurityLogger
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Log the request
        $this->securityService->logEvent(
            'api_request',
            'info',
            [
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return $next($request);
    }
}
