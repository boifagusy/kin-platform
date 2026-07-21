<?php

namespace App\Http\Middleware;

use App\Services\IdempotencyService;
use Closure;
use Illuminate\Http\Request;

class IdempotencyMiddleware
{
    public function __construct(private IdempotencyService $idempotency) {}

    public function handle(Request $request, Closure $next)
    {
        $operationId = $request->header('X-Operation-Id');

        // No header — process normally (backward compatible)
        if (!$operationId) {
            return $next($request);
        }

        // Invalid UUID — ignore and process normally
        if (!$this->idempotency->isValidUuid($operationId)) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $endpoint = $request->path();

        // Check cache for duplicate
        $cached = $this->idempotency->getCachedResponse($endpoint, $user->id, $operationId);
        if ($cached) {
            return response()->json($cached['response_body'], $cached['status_code']);
        }

        // Process request
        $response = $next($request);

        // Cache only successful responses (2xx)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $responseBody = json_decode($response->getContent(), true) ?: [];
            $this->idempotency->cacheResponse(
                $endpoint,
                $user->id,
                $operationId,
                $response->getStatusCode(),
                $responseBody
            );
        }

        return $response;
    }
}
