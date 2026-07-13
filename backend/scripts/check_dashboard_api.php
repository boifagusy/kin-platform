<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('phone', '+2348086448522')->first();
if (!$user) { echo 'User not found'; exit(1); }

$snapshot = app(App\Services\DashboardSnapshotService::class)->getSnapshot($user);

echo "API DASHBOARD RESPONSE:" . PHP_EOL;
echo str_repeat('-', 50) . PHP_EOL;
echo 'safety_score:      ' . ($snapshot['safety_score'] ?? 'MISSING') . PHP_EOL;
echo 'recent_checkin:    ' . var_export($snapshot['recent_checkin'] ?? 'MISSING', true) . PHP_EOL;
echo 'has_verified_contact: ' . var_export($snapshot['has_verified_contact'] ?? 'MISSING', true) . PHP_EOL;

echo PHP_EOL . 'TRUSTED CONTACT:' . PHP_EOL;
echo str_repeat('-', 50) . PHP_EOL;
$tc = $snapshot['trusted_contact'] ?? null;
if ($tc) {
    echo 'name:  ' . ($tc['name'] ?? 'N/A') . PHP_EOL;
    echo 'phone: ' . ($tc['phone'] ?? 'N/A') . PHP_EOL;
} else {
    echo 'NO TRUSTED CONTACT' . PHP_EOL;
}

echo PHP_EOL . 'PENDING TASKS:' . PHP_EOL;
echo str_repeat('-', 50) . PHP_EOL;
foreach (($snapshot['pending_tasks'] ?? []) as $task) {
    echo '  - ' . ($task['title'] ?? $task['id'] ?? 'unknown') . PHP_EOL;
}
echo 'Total: ' . count($snapshot['pending_tasks'] ?? []) . PHP_EOL;

echo PHP_EOL . 'USER DATA:' . PHP_EOL;
echo str_repeat('-', 50) . PHP_EOL;
$userData = $snapshot['user'] ?? [];
echo 'name:  ' . ($userData['name'] ?? 'N/A') . PHP_EOL;
echo 'phone: ' . ($userData['phone'] ?? 'N/A') . PHP_EOL;
echo 'contacts_count: ' . ($userData['contacts_count'] ?? 0) . PHP_EOL;
