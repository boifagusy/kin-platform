<?php

namespace App\Services\Watchtower;

use App\Models\WatchtowerMetric;
use Illuminate\Support\Facades\DB;

class TrendService
{
    public function getTrend(string $metric, string $interval = 'hour'): array
    {
        $timeRanges = [
            'hour' => now()->subHour(),
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subMonths(3),
        ];

        $startTime = $timeRanges[$interval] ?? now()->subDay();

        $data = WatchtowerMetric::where('name', $metric)
            ->where('collected_at', '>=', $startTime)
            ->orderBy('collected_at', 'asc')
            ->get();

        return [
            'metric' => $metric,
            'interval' => $interval,
            'data_points' => $data->count(),
            'data' => $data->map(function ($item) {
                return [
                    'value' => $item->value,
                    'timestamp' => $item->collected_at->toISOString(),
                    'labels' => $item->labels,
                ];
            }),
            'summary' => [
                'min' => $data->min('value'),
                'max' => $data->max('value'),
                'avg' => round($data->avg('value'), 2),
                'current' => $data->last()->value ?? 0,
                'trend' => $this->calculateTrend($data),
            ],
        ];
    }

    protected function calculateTrend($data): string
    {
        if ($data->count() < 2) {
            return 'stable';
        }

        $first = $data->first()->value;
        $last = $data->last()->value;
        $diff = $last - $first;

        if (abs($diff) < 0.01) {
            return 'stable';
        }

        return $diff > 0 ? 'increasing' : 'decreasing';
    }

    public function getDashboardTrends(): array
    {
        $metrics = [
            'api_response_time',
            'error_rate',
            'queue_size',
            'storage_used_percent',
            'plugin_uptime',
            'memory_usage',
        ];

        $trends = [];
        foreach ($metrics as $metric) {
            $trends[$metric] = $this->getTrend($metric, 'hour');
        }

        return $trends;
    }
}
