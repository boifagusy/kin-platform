<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RateLimiter
{
    private int $maxPerHour;
    private int $maxPerDay;

    public function __construct(int $maxPerHour = 10, int $maxPerDay = 50)
    {
        $this->maxPerHour = $maxPerHour;
        $this->maxPerDay = $maxPerDay;
    }

    public function isLimited(int $userId, string $channel): bool
    {
        $hourKey = "rate_limit:{$userId}:{$channel}:" . now()->format('Y-m-d-H');
        $dayKey = "rate_limit:{$userId}:{$channel}:" . now()->format('Y-m-d');

        $hourCount = Cache::get($hourKey, 0);
        $dayCount = Cache::get($dayKey, 0);

        return $hourCount >= $this->maxPerHour || $dayCount >= $this->maxPerDay;
    }

    public function increment(int $userId, string $channel): void
    {
        $hourKey = "rate_limit:{$userId}:{$channel}:" . now()->format('Y-m-d-H');
        $dayKey = "rate_limit:{$userId}:{$channel}:" . now()->format('Y-m-d');

        Cache::increment($hourKey, 1, now()->addHour());
        Cache::increment($dayKey, 1, now()->addDay());
    }

    public function getCount(int $userId, string $channel): array
    {
        $hourKey = "rate_limit:{$userId}:{$channel}:" . now()->format('Y-m-d-H');
        $dayKey = "rate_limit:{$userId}:{$channel}:" . now()->format('Y-m-d');

        return [
            'hour' => Cache::get($hourKey, 0),
            'day' => Cache::get($dayKey, 0),
            'max_per_hour' => $this->maxPerHour,
            'max_per_day' => $this->maxPerDay,
        ];
    }
}
