<?php

namespace App\Services\Pulse;

class ScoreLevels
{
    public const SAFE = 'safe';
    public const MONITOR = 'monitor';
    public const AT_RISK = 'at_risk';
    public const EMERGENCY = 'emergency';
    
    public const LEVELS = [
        self::SAFE => [
            'label' => 'Safe',
            'icon' => '🟢',
            'min_score' => 80,
            'max_score' => 100,
            'color' => 'green'
        ],
        self::MONITOR => [
            'label' => 'Monitor',
            'icon' => '🟡',
            'min_score' => 50,
            'max_score' => 79,
            'color' => 'yellow'
        ],
        self::AT_RISK => [
            'label' => 'At Risk',
            'icon' => '🟠',
            'min_score' => 30,
            'max_score' => 49,
            'color' => 'orange'
        ],
        self::EMERGENCY => [
            'label' => 'Emergency',
            'icon' => '🔴',
            'min_score' => 0,
            'max_score' => 29,
            'color' => 'red'
        ]
    ];
    
    public static function getLevel(int $score): string
    {
        foreach (self::LEVELS as $level => $config) {
            if ($score >= $config['min_score'] && $score <= $config['max_score']) {
                return $level;
            }
        }
        return self::SAFE;
    }
    
    public static function getLevelConfig(string $level): array
    {
        return self::LEVELS[$level] ?? self::LEVELS[self::SAFE];
    }
}
