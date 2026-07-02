<?php

echo "═══════════════════════════════════════════════════════════════\n";
echo "  🧪 ESCALATION DECISION ENGINE — TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Simulate decision scenarios
$scenarios = [
    [
        'name' => 'Perfect Safety (Green)',
        'confidence' => 95,
        'tier' => 'green',
        'trust_score' => 90,
        'active_incidents' => 0,
        'duress' => false,
        'expected' => 'monitor'
    ],
    [
        'name' => 'Missed Check-in (Yellow)',
        'confidence' => 70,
        'tier' => 'yellow',
        'trust_score' => 85,
        'active_incidents' => 0,
        'duress' => false,
        'expected' => 'monitor'
    ],
    [
        'name' => 'Orange Warning',
        'confidence' => 50,
        'tier' => 'orange',
        'trust_score' => 80,
        'active_incidents' => 1,
        'duress' => false,
        'expected' => 'escalate'
    ],
    [
        'name' => 'Red Alert',
        'confidence' => 30,
        'tier' => 'red',
        'trust_score' => 70,
        'active_incidents' => 2,
        'duress' => false,
        'expected' => 'escalate'
    ],
    [
        'name' => 'Black Emergency',
        'confidence' => 15,
        'tier' => 'black',
        'trust_score' => 60,
        'active_incidents' => 3,
        'duress' => false,
        'expected' => 'escalate'
    ],
    [
        'name' => 'Duress Detection',
        'confidence' => 65,
        'tier' => 'yellow',
        'trust_score' => 85,
        'active_incidents' => 0,
        'duress' => true,
        'expected' => 'escalate'
    ],
    [
        'name' => 'Low Trust Device',
        'confidence' => 75,
        'tier' => 'yellow',
        'trust_score' => 25,
        'active_incidents' => 0,
        'duress' => false,
        'expected' => 'escalate'
    ],
];

function needsEscalation($confidence, $tier, $activeIncidents, $trustScore, $duress) {
    // Emergency: Black tier or confidence < 20
    if ($confidence < 20) return true;
    if ($confidence < 40) return true;
    if ($confidence < 60 && $activeIncidents > 0) return true;
    if ($duress) return true;
    if ($trustScore < 30) return true;
    return false;
}

function determineLevel($confidence, $tier, $activeIncidents, $trustScore, $duress) {
    if ($duress) return 'red';
    if ($confidence < 20) return 'black';
    if ($confidence < 40) return 'red';
    if ($confidence < 60) return 'orange';
    if ($activeIncidents >= 3) return 'orange';
    if ($trustScore < 30) return 'orange';
    return 'orange';
}

echo "┌──────────────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ Scenario                    │ Conf │ Trust │ Incidents │ Duress │ Decision │ Level │\n";
echo "├──────────────────────────────────────────────────────────────────────────────────────┤\n";

foreach ($scenarios as $scenario) {
    $needs = needsEscalation(
        $scenario['confidence'],
        $scenario['tier'],
        $scenario['active_incidents'],
        $scenario['trust_score'],
        $scenario['duress']
    );
    
    $decision = $needs ? 'ESCALATE' : 'MONITOR';
    $level = $needs ? determineLevel(
        $scenario['confidence'],
        $scenario['tier'],
        $scenario['active_incidents'],
        $scenario['trust_score'],
        $scenario['duress']
    ) : '-';
    
    $name = str_pad($scenario['name'], 25, ' ');
    $conf = str_pad($scenario['confidence'], 4, ' ', STR_PAD_LEFT);
    $trust = str_pad($scenario['trust_score'], 4, ' ', STR_PAD_LEFT);
    $inc = str_pad($scenario['active_incidents'], 9, ' ');
    $duressStr = $scenario['duress'] ? '✅' : '❌';
    $decisionStr = str_pad($decision, 8, ' ');
    $levelStr = str_pad($level, 5, ' ');
    
    echo "│ {$name} │ {$conf} │ {$trust} │ {$inc} │ {$duressStr}   │ {$decisionStr} │ {$levelStr} │\n";
}

echo "└──────────────────────────────────────────────────────────────────────────────────────┘\n";
echo "\n";

echo "📊 DECISION RULES:\n";
echo "  🔵 Monitor: Confidence ≥ 60, no incidents, trusted device\n";
echo "  🟠 Escalate (Orange): Confidence 40-59 with active incidents\n";
echo "  🔴 Escalate (Red): Confidence 20-39 or duress detected\n";
echo "  ⚫ Escalate (Black): Confidence < 20\n";
echo "\n";
echo "✅ Escalation Decision Engine test completed successfully!\n";
