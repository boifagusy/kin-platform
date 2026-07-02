<?php

namespace App\Services\Watchtower;

class HealthScoreService
{
    public function getDetailedHealthScore(array $components): array
    {
        $breakdown = [];
        $total = 0;
        $count = 0;

        foreach ($components as $name => $status) {
            $score = $this->calculateComponentScore($status);
            $breakdown[$name] = $score;
            $total += $score['score'];
            $count++;
        }

        $overall = $count > 0 ? round($total / $count) : 0;

        return [
            'overall' => $overall,
            'breakdown' => $breakdown,
            'components' => $components,
        ];
    }

    private function calculateComponentScore(string $status): array
    {
        $scoreMap = [
            'healthy' => ['score' => 100, 'label' => 'Excellent'],
            'ok' => ['score' => 90, 'label' => 'Good'],
            'degraded' => ['score' => 70, 'label' => 'Degraded'],
            'warning' => ['score' => 60, 'label' => 'Warning'],
            'unhealthy' => ['score' => 40, 'label' => 'Unhealthy'],
            'critical' => ['score' => 20, 'label' => 'Critical'],
            'unknown' => ['score' => 50, 'label' => 'Unknown'],
        ];

        $result = $scoreMap[$status] ?? $scoreMap['unknown'];
        $result['status'] = $status;
        return $result;
    }
}
