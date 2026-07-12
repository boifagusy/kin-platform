<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = $app->make(\App\Http\Controllers\Admin\WatchtowerDashboardController::class);
$request = Illuminate\Http\Request::create('/admin/watchtower', 'GET');
$response = $controller->index($request);
$data = $response->getData();

echo "=== Inspecting 'health' field from first 3 modules ===" . PHP_EOL . PHP_EOL;

$count = 0;
foreach ($data['modules'] as $key => $module) {
    if ($count >= 3) break;
    
    echo $module['label'] . ':' . PHP_EOL;
    echo '  health type: ' . gettype($module['health']) . PHP_EOL;
    echo '  health value: ';
    print_r($module['health']);
    echo PHP_EOL;
    
    $count++;
}

// Check if all services return array for overall_health
echo "=== Checking all services' overall_health from contract ===" . PHP_EOL . PHP_EOL;

$contractFile = __DIR__ . '/../storage/app/contracts/watchtower_services/latest.json';
if (file_exists($contractFile)) {
    $contract = json_decode(file_get_contents($contractFile), true);
    
    foreach ($contract['contracts'] as $name => $info) {
        echo $name . ': keys = ' . implode(', ', $info['keys']) . PHP_EOL;
    }
    
    echo PHP_EOL . "NOTE: 'overall_health' appears in every service but its structure";
    echo PHP_EOL . "      needs to be inspected per service." . PHP_EOL;
}

