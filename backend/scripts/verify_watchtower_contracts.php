<?php

/**
 * Watchtower Service Contract Verification
 * 
 * Verifies service contracts and generates machine-readable artifact.
 * Does NOT certify — certification belongs to Governance.
 * 
 * Brick: watchtower_dashboard
 * Task:  0 — Contract Verification
 * OS:    v3.2-RC1
 */

$started = microtime(true);

echo "══════════════════════════════════════════" . PHP_EOL;
echo "  WATCHTOWER SERVICE CONTRACT VERIFICATION" . PHP_EOL;
echo "  OS: v3.2-RC1 | Task: 0" . PHP_EOL;
echo "══════════════════════════════════════════" . PHP_EOL;
echo PHP_EOL;

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel bootstrapped: OK" . PHP_EOL . PHP_EOL;

// Service contract definitions
$contracts = [
    \App\Services\Watchtower\ApiMonitorService::class             => 'getMetrics',
    \App\Services\Watchtower\QueueMonitorService::class           => 'getMetrics',
    \App\Services\Watchtower\DatabaseMonitorService::class        => 'getMetrics',
    \App\Services\Watchtower\PluginHealthService::class           => 'getPluginHealth',
    \App\Services\Watchtower\SafetyEngineMonitorService::class    => 'getMetrics',
    \App\Services\Watchtower\PerformanceMonitorService::class     => 'getMetrics',
    \App\Services\Watchtower\ErrorMonitorService::class           => 'getMetrics',
    \App\Services\Watchtower\NotificationMonitorService::class    => 'getMetrics',
    \App\Services\Watchtower\SecurityMonitorService::class        => 'getMetrics',
    \App\Services\Watchtower\WatchtowerHealthService::class       => 'getHealth',
];

$results = [];
$passes = 0;
$failures = 0;

foreach ($contracts as $class => $method) {
    $serviceName = class_basename($class);
    $warnings = [];
    
    try {
        $service = $app->make($class);
        
        if (!method_exists($service, $method)) {
            throw new \Exception("Method '$method' not found");
        }
        
        $data = $service->$method();
        
        if (!is_array($data)) {
            throw new \Exception("Expected array, got " . gettype($data));
        }
        
        if (array_is_list($data)) {
            $warnings[] = "Returned indexed array (expected associative)";
        }
        
        $keys = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                throw new \Exception("Invalid metric key type: " . gettype($key));
            }
            $keys[] = $key;
        }
        
        echo "✅ {$serviceName}::{$method}()" . PHP_EOL;
        echo "   Return: array" . PHP_EOL;
        echo "   Keys: " . implode(', ', $keys) . PHP_EOL;
        
        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                echo "   ⚠️  {$warning}" . PHP_EOL;
            }
        }
        
        $results[$serviceName] = [
            'status' => 'PASS',
            'class' => $class,
            'method' => $method,
            'return_type' => 'array',
            'keys' => $keys,
            'key_count' => count($keys),
            'warnings' => $warnings,
        ];
        $passes++;
        
    } catch (\Exception $e) {
        echo "❌ {$serviceName}::{$method}()" . PHP_EOL;
        echo "   Error: " . $e->getMessage() . PHP_EOL;
        
        $results[$serviceName] = [
            'status' => 'FAIL',
            'class' => $class,
            'method' => $method,
            'error' => $e->getMessage(),
        ];
        $failures++;
    }
    
    echo PHP_EOL;
}

// Calculate duration
$durationMs = round((microtime(true) - $started) * 1000, 2);
$timestamp = date('Ymd\THis');
$verifiedAt = date('c');

// Summary
echo "══════════════════════════════════════════" . PHP_EOL;
echo "  VERIFICATION SUMMARY" . PHP_EOL;
echo "══════════════════════════════════════════" . PHP_EOL . PHP_EOL;
echo "Services:  {$passes}/" . count($contracts) . PHP_EOL;
echo "Methods:   {$passes}/" . count($contracts) . PHP_EOL;
echo "Types:     {$passes}/" . count($contracts) . PHP_EOL;
echo "Contracts: {$passes}/" . count($contracts) . PHP_EOL;
echo "Duration:  {$durationMs}ms" . PHP_EOL . PHP_EOL;

// Build artifact
$artifact = [
    'contract_version' => '1.0',
    'brick' => 'watchtower_dashboard',
    'task' => '0',
    'gate' => '6',
    'project' => 'KIN',
    'os_version' => '3.2-RC1',
    'verified_at' => $verifiedAt,
    'duration_ms' => $durationMs,
    'total_services' => count($contracts),
    'passed' => $passes,
    'failed' => $failures,
    'result' => $failures === 0 ? 'PASS' : 'FAIL',
    'contracts' => $results,
];

// Write artifact with checksum
$artifactDir = __DIR__.'/../storage/app/contracts/watchtower_services';
if (!is_dir($artifactDir)) {
    mkdir($artifactDir, 0755, true);
}

$artifactFile = "{$artifactDir}/{$timestamp}.json";
$latestFile = "{$artifactDir}/latest.json";

// Write initial artifact (without hash)
file_put_contents($artifactFile, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Generate checksum
$hash = hash_file('sha256', $artifactFile);
$artifact['hash'] = $hash;

// Re-write with checksum included
file_put_contents($artifactFile, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($latestFile, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Artifact: {$artifactFile}" . PHP_EOL;
echo "Latest:   {$latestFile}" . PHP_EOL;
echo "SHA256:   {$hash}" . PHP_EOL . PHP_EOL;

if ($failures === 0) {
    echo "══════════════════════════════════════════" . PHP_EOL;
    echo "  VERIFICATION RESULT" . PHP_EOL;
    echo "══════════════════════════════════════════" . PHP_EOL . PHP_EOL;
    echo "Result:   PASS" . PHP_EOL;
    echo "Status:   Verification Passed" . PHP_EOL;
    echo "Awaiting: Governance Certification" . PHP_EOL;
    echo "Command:  ai contract certify watchtower_services" . PHP_EOL;
    echo PHP_EOL;
    exit(0);
} else {
    echo "RESULT: FAIL ({$failures} service(s) failed)" . PHP_EOL;
    echo "Task 1 (Configuration) remains blocked." . PHP_EOL;
    exit(1);
}
