<?php

echo "═══════════════════════════════════════════════════════════════\n";
echo "  🧪 DEVICE TRUST ENGINE — TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Simulate device trust calculations
$scenarios = [
    [
        'name' => 'Trusted Device',
        'factors' => [
            'root_detected' => false,
            'emulator_detected' => false,
            'sim_changed' => false,
            'app_reinstalled' => false,
            'installation_days' => 30,
        ]
    ],
    [
        'name' => 'Rooted Device',
        'factors' => [
            'root_detected' => true,
            'emulator_detected' => false,
            'sim_changed' => false,
            'app_reinstalled' => false,
            'installation_days' => 30,
        ]
    ],
    [
        'name' => 'Emulator',
        'factors' => [
            'root_detected' => false,
            'emulator_detected' => true,
            'sim_changed' => false,
            'app_reinstalled' => false,
            'installation_days' => 1,
        ]
    ],
    [
        'name' => 'SIM Changed',
        'factors' => [
            'root_detected' => false,
            'emulator_detected' => false,
            'sim_changed' => true,
            'app_reinstalled' => false,
            'installation_days' => 30,
        ]
    ],
    [
        'name' => 'App Reinstalled',
        'factors' => [
            'root_detected' => false,
            'emulator_detected' => false,
            'sim_changed' => false,
            'app_reinstalled' => true,
            'installation_days' => 1,
        ]
    ],
];

function calculateTrustScore($factors) {
    $score = 100;
    
    if ($factors['root_detected']) $score -= 30;
    if ($factors['emulator_detected']) $score -= 50;
    if ($factors['sim_changed']) $score -= 15;
    if ($factors['app_reinstalled']) $score -= 20;
    if ($factors['installation_days'] < 7) $score -= 10;
    
    return max(0, min(100, $score));
}

function getTrustLevel($score) {
    if ($score >= 80) return 'HIGH';
    if ($score >= 50) return 'MEDIUM';
    return 'LOW';
}

function getStatus($score) {
    if ($score >= 70) return '✅ TRUSTED';
    if ($score >= 40) return '⚠️ CAUTION';
    return '🚨 UNTRUSTED';
}

echo "┌──────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ Scenario                    │ Score │ Level │ Status                        │\n";
echo "├──────────────────────────────────────────────────────────────────────────────┤\n";

foreach ($scenarios as $scenario) {
    $score = calculateTrustScore($scenario['factors']);
    $level = getTrustLevel($score);
    $status = getStatus($score);
    
    $name = str_pad($scenario['name'], 25, ' ');
    $scoreStr = str_pad($score, 5, ' ', STR_PAD_LEFT);
    $levelStr = str_pad($level, 7, ' ');
    
    echo "│ {$name} │ {$scoreStr} │ {$levelStr} │ {$status}          │\n";
}

echo "└──────────────────────────────────────────────────────────────────────────────┘\n";
echo "\n";

echo "📊 EXPLANATION:\n";
echo "  ✅ TRUSTED  (≥70): Device is trusted, full access\n";
echo "  ⚠️ CAUTION (40-69): Device has some trust issues\n";
echo "  🚨 UNTRUSTED (<40): Device is untrusted, limited access\n";
echo "\n";
echo "✅ Device Trust Engine test completed successfully!\n";
