<?php

echo "══════════════════════════════════════════" . PHP_EOL;
echo "  PRESENTATION INVESTIGATION REPORT" . PHP_EOL;
echo "══════════════════════════════════════════" . PHP_EOL . PHP_EOL;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = $app->make(\App\Http\Controllers\Admin\WatchtowerDashboardController::class);
$request = Illuminate\Http\Request::create('/admin/watchtower', 'GET');
$response = $controller->index($request);
$html = $response->render();
$data = $response->getData();

echo "=== BLADE ANALYSIS ===" . PHP_EOL;
echo "json_encode calls in rendered HTML: " . substr_count($html, 'json_encode') . PHP_EOL;
echo "Array to string conversion risk: YES (if any module passes nested arrays)" . PHP_EOL;
echo PHP_EOL;

foreach ($data['modules'] as $key => $module) {
    echo str_repeat('─', 60) . PHP_EOL;
    echo "MODULE: {$module['label']} ({$key})" . PHP_EOL;
    echo str_repeat('─', 60) . PHP_EOL;
    echo "Health: {$module['health']}" . PHP_EOL;
    echo "Status: {$module['status']}" . PHP_EOL;
    
    if ($module['status'] === 'error') {
        echo "ERROR STATE: {$module['error']}" . PHP_EOL;
    }
    
    if (!empty($module['metrics'])) {
        echo PHP_EOL . "Metrics passed to Blade:" . PHP_EOL;
        foreach ($module['metrics'] as $mKey => $mValue) {
            $type = gettype($mValue);
            $display = $type === 'array' ? 'ARRAY(' . count($mValue) . ')' : (is_numeric($mValue) ? $mValue : substr((string)$mValue, 0, 60));
            $needsPresenter = $type === 'array' ? '❌ RAW' : '✅';
            echo "  {$needsPresenter} {$mKey}: [{$type}] {$display}" . PHP_EOL;
        }
    }
    
    echo PHP_EOL;
}

echo str_repeat('═', 60) . PHP_EOL;
echo "GAP ANALYSIS" . PHP_EOL;
echo str_repeat('═', 60) . PHP_EOL . PHP_EOL;

$needsPresenter = 0;
foreach ($data['modules'] as $key => $module) {
    $hasArray = false;
    if (!empty($module['metrics'])) {
        foreach ($module['metrics'] as $value) {
            if (is_array($value)) { $hasArray = true; break; }
        }
    }
    echo sprintf("  %-20s %s", $module['label'], $hasArray ? '❌ Needs Presenter' : '✅ OK') . PHP_EOL;
    if ($hasArray) $needsPresenter++;
}

echo PHP_EOL . "Modules needing presenter: {$needsPresenter}/10" . PHP_EOL;
echo "json_encode() in Blade: " . (substr_count(file_get_contents(__DIR__.'/../resources/views/admin/watchtower/index.blade.php'), 'json_encode') > 0 ? 'YES — violates Presentation Rule' : 'NONE') . PHP_EOL;
echo PHP_EOL;
echo "Status: Investigation complete. Awaiting approval for presenter implementation." . PHP_EOL;
