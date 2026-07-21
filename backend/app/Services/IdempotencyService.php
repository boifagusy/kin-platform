<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class IdempotencyService
{
    private const CACHE_TTL_MINUTES = 5;
    private const KEY_PREFIX = 'idempotent:v1';

    /**
     * Check if an operation has already been processed.
     * Returns cached response array or null.
     */
    public function getCachedResponse(string $endpoint, int $userId, string $operationId): ?array
    {
        $key = $this->buildKey($endpoint, $userId, $operationId);
        return Cache::get($key);
    }

    /**
     * Cache a successful response for future deduplication.
     */
    public function cacheResponse(string $endpoint, int $userId, string $operationId, int $statusCode, array $responseBody): void
    {
        $key = $this->buildKey($endpoint, $userId, $operationId);

        Cache::put($key, [
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'cached_at' => now()->toIso8601String(),
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));
    }

    /**
     * Validate UUID format. Returns true if valid, false otherwise.
     */
    public function isValidUuid(string $operationId): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $operationId) === 1;
    }

    private function buildKey(string $endpoint, int $userId, string $operationId): string
    {
        return self::KEY_PREFIX . ":{$endpoint}:{$userId}:{$operationId}";
    }
}
