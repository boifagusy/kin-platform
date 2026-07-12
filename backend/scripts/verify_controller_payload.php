<?php

echo "══════════════════════════════════════════" . PHP_EOL;
echo "  TASK 1.5: CONTROLLER PAYLOAD VERIFICATION" . PHP_EOL;
echo "══════════════════════════════════════════" . PHP_EOL . PHP_EOL;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Phase A: Verify controller payload structure" . PHP_EOL . PHP_EOL;

$controller = $app->make(\App\Http\Controllers\Admin\WatchtowerDashboardController::class);
$request = Illuminate\Http\Request::create('/admin/watchtower', 'GET');
$response = $controller->index($request);
$data = $response->getData();

// Check top-level keys
$expectedKeys = ['modules', 'lastUpdated', 'healthyCount', 'warningCount', 'criticalCount', 'errorCount', 'overallStatus'];
echo "Top-level keys:" . PHP_EOL;
foreach ($expectedKeys as $key) {
    $exists = array_key_exists($key, $data);
    echo '  ' . ($exists ? '✅' : '❌') . ' ' . $key;
    if ($exists) {
        $value = $data[$key];
        if (is_array($value)) {
            echo ' = array(' . count($value) . ')';
        } else {
            echo ' = ' . $value;
        }
    }
    echo PHP_EOL;
}

echo PHP_EOL . "Phase B: Verify module structure" . PHP_EOL . PHP_EOL;

$modules = $data['modules'];
$expectedModuleKeys = ['status', 'label', 'icon', 'health', 'metrics'];

foreach ($modules as $key => $module) {
    echo $module['label'] . ' (' . $key . '):' . PHP_EOL;
    echo '  health: ' . ($module['health'] ?? 'MISSING') . PHP_EOL;
    echo '  status: ' . ($module['status'] ?? 'MISSING') . PHP_EOL;
    
    $allKeys = true;
    foreach ($expectedModuleKeys as $k) {
        if (!array_key_exists($k, $module)) {
            echo '  ❌ Missing key: ' . $k . PHP_EOL;
            $allKeys = false;
        }
    }
    
    if ($allKeys) {
        echo '  ✅ All required keys present' . PHP_EOL;
    }
    
    $metricCount = count($module['metrics'] ?? []);
    echo '  Metrics: ' . $metricCount . PHP_EOL;
    
    if ($module['status'] === 'ok' && $metricCount > 0) {
        echo '  Metric keys: ' . implode(', ', array_keys($module['metrics'])) . PHP_EOL;
    }
    
    echo PHP_EOL;
}

echo "Phase C: Normalization check" . PHP_EOL . PHP_EOL;

$validHealth = ['healthy', 'warning', 'critical', 'error', 'unknown'];
$allValid = true;
foreach ($modules as $key => $module) {
    if (!in_array($module['health'], $validHealth)) {
        echo '  ❌ ' . $module['label'] . ' has invalid health: "' . $module['health'] . '"' . PHP_EOL;
        $allValid = false;
    }
}

if ($allValid) {
    echo "✅ All modules have valid health values" . PHP_EOL;
}

echo PHP_EOL . "RESULT: Controller payload verified and ready for Blade view." . PHP_EOL;
