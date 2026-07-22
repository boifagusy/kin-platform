<?php
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = [
    'type' => 'trusted_contact_invitation',
    'title' => 'Trusted Contact Invitation',
    'description' => 'Idowu wants to add you as a trusted contact.',
    'icon' => 'person_add',
    'actions' => [
        ['label' => 'Accept', 'action' => 'accept', 'variant' => 'primary'],
        ['label' => 'Decline', 'action' => 'decline', 'variant' => 'secondary']
    ]
];

$n = App\Models\IncidentNotification::create([
    'incident_id' => 703,
    'message' => 'Idowu wants to add you as a trusted contact.',
    'status' => 'delivered',
    'registry_key' => 'trusted_contact_invitation',
    'trigger' => 'SERVER_EVENT',
    'priority' => 3,
    'action_required' => true,
    'action_completed' => false,
    'action_data' => $data,
    'storage_policy' => 'SERVER_WITH_LOCAL_CACHE',
    'lifecycle_state' => 'CREATED',
]);

echo 'ID: ' . $n->id . PHP_EOL;
