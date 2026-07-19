<?php

echo "═══════════════════════════════════════════════════════════════\n";
echo "  🧪 QUICK SAFETY CONFIDENCE CALCULATION TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Define tiers
$tiers = [
    'green' => ['min' => 80, 'max' => 100, 'emoji' => '🟢'],
    'yellow' => ['min' => 60, 'max' => 79, 'emoji' => '🟡'],
    'orange' => ['min' => 40, 'max' => 59, 'emoji' => '🟠'],
    'red' => ['min' => 20, 'max' => 39, 'emoji' => '🔴'],
    'black' => ['min' => 0, 'max' => 19, 'emoji' => '⚫'],
];

function getTier($score) {
    global $tiers;
    foreach ($tiers as $tier => $range) {
        if ($score >= $range['min'] && $score <= $range['max']) {
            return $tier;
        }
    }
    return 'unknown';
}

function getTierEmoji($tier) {
    global $tiers;
    return $tiers[$tier]['emoji'] ?? '❓';
}

// Simulate scores for different scenarios
$scenarios = [
    [
        'name' => 'Perfect Safety',
        'factors' => ['checkin' => 1.0, 'contacts' => 1.0, 'duress' => 1.0, 'history' => 1.0, 'activity' => 0.9, 'device_trust' => 1.0],
        'penalty' => 0
    ],
    [
        'name' => 'Missed Check-in (12h)',
        'factors' => ['checkin' => 0.5, 'contacts' => 1.0, 'duress' => 1.0, 'history' => 0.8, 'activity' => 0.9, 'device_trust' => 0.8],
        'penalty' => 5
    ],
    [
        'name' => 'Duress Activation',
        'factors' => ['checkin' => 0.4, 'contacts' => 0.7, 'duress' => 1.0, 'history' => 0.8, 'activity' => 0.6, 'device_trust' => 0.8],
        'penalty' => 15
    ],
    [
        'name' => 'Multiple Incidents',
        'factors' => ['checkin' => 0.4, 'contacts' => 0.0, 'duress' => 0.3, 'history' => 0.2, 'activity' => 0.6, 'device_trust' => 0.8],
        'penalty' => 20
    ],
    [
        'name' => 'No Setup',
        'factors' => ['checkin' => 0.2, 'contacts' => 0.0, 'duress' => 0.3, 'history' => 0.2, 'activity' => 0.9, 'device_trust' => 0.5],
        'penalty' => 0
    ],
    [
        'name' => 'Full Recovery',
        'factors' => ['checkin' => 1.0, 'contacts' => 1.0, 'duress' => 1.0, 'history' => 1.0, 'activity' => 0.9, 'device_trust' => 1.0],
        'penalty' => 0
    ],
];

// Weights
$weights = [
    'checkin' => 0.35,
    'contacts' => 0.15,
    'duress' => 0.20,
    'history' => 0.10,
    'activity' => 0.10,
    'device_trust' => 0.10,
];

echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ Scenario                    │ Score │ Tier │ Status                         │\n";
echo "├─────────────────────────────────────────────────────────────────────────────┤\n";

foreach ($scenarios as $scenario) {
    // Calculate weighted score
    $total = 0;
    foreach ($scenario['factors'] as $key => $value) {
        $total += ($weights[$key] ?? 0) * $value;
    }
    $score = round($total * 100 - $scenario['penalty']);
    $score = max(0, min(100, $score));
    $tier = getTier($score);
    $emoji = getTierEmoji($tier);
    
    $name = str_pad($scenario['name'], 25, ' ');
    $scoreStr = str_pad($score, 5, ' ', STR_PAD_LEFT);
    $tierStr = str_pad(strtoupper($tier), 6, ' ');
    $status = ($score >= 80) ? '✅ SAFE' : (($score >= 40) ? '⚠️ WATCH' : '🚨 ALERT');
    
    echo "│ {$name} │ {$scoreStr} │ {$emoji}{$tierStr} │ {$status}          │\n";
}

echo "└─────────────────────────────────────────────────────────────────────────────┘\n";
echo "\n";

echo "📊 EXPLANATION:\n";
echo "  🟢 Green  (80-100): Safe - Normal operation\n";
echo "  🟡 Yellow (60-79):  Monitor - Keep watching\n";
echo "  🟠 Orange (40-59):  Investigate - Check on user\n";
echo "  🔴 Red    (20-39):  Alert - Urgent action needed\n";
echo "  ⚫ Black  (0-19):   Emergency - Full protocol\n";
echo "\n";
echo "✅ Simulation completed successfully!\n";
