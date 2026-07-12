<?php

echo "══════════════════════════════════════════" . PHP_EOL;
echo "  PRESENTER CONTRACT INVESTIGATION" . PHP_EOL;
echo "══════════════════════════════════════════" . PHP_EOL . PHP_EOL;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = $app->make(\App\Http\Controllers\Admin\WatchtowerDashboardController::class);
$request = Illuminate\Http\Request::create('/admin/watchtower', 'GET');
$response = $controller->index($request);
$data = $response->getData();

function dumpKeys($arr, $indent = '    ') {
    foreach ($arr as $key => $value) {
        if (is_array($value) && !array_is_list($value)) {
            echo "{$indent}{$key}" . PHP_EOL;
            dumpKeys($value, $indent . '  ');
        } elseif (is_array($value)) {
            $count = count($value);
            echo "{$indent}{$key}[] ({$count} items)" . PHP_EOL;
            if ($count > 0 && is_array($value[0] ?? null)) {
                echo "{$indent}  keys: " . implode(', ', array_keys($value[0])) . PHP_EOL;
            }
        } else {
            $display = is_null($value) ? 'null' : (is_bool($value) ? ($value ? 'true' : 'false') : (string)$value);
            echo "{$indent}{$key}: {$display}" . PHP_EOL;
        }
    }
}

foreach ($data['modules'] as $key => $module) {
    echo str_repeat('═', 60) . PHP_EOL;
    echo "MODULE: {$module['label']} ({$key})" . PHP_EOL;
    echo str_repeat('═', 60) . PHP_EOL;
    
    if ($module['status'] === 'error') {
        echo "STATE: Error — {$module['error']}" . PHP_EOL;
        echo PHP_EOL;
        continue;
    }
    
    echo "Current health: {$module['health']}" . PHP_EOL;
    echo PHP_EOL;
    echo "Raw contract (nested keys):" . PHP_EOL;
    
    foreach ($module['metrics'] as $mKey => $mValue) {
        if (is_array($mValue)) {
            echo "  {$mKey}:" . PHP_EOL;
            dumpKeys($mValue, '    ');
        }
    }
    
    echo PHP_EOL;
}

echo str_repeat('═', 60) . PHP_EOL;
echo "HEALTH SOURCE SUMMARY" . PHP_EOL;
echo str_repeat('═', 60) . PHP_EOL . PHP_EOL;

foreach ($data['modules'] as $key => $module) {
    $hasOverall = isset($module['metrics']['overall_health']);
    $hasStatus = isset($module['metrics']['status']);
    $source = $hasOverall ? 'overall_health.status' : ($hasStatus ? 'status' : 'unknown');
    echo "  {$module['label']}: health from {$source} = {$module['health']}" . PHP_EOL;
}

echo PHP_EOL . "Investigation complete. All nested keys documented." . PHP_EOL;
