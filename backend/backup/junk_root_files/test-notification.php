<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('phone', 'like', '%234%')->first();
if (!$user) {
    echo "No user found\n";
    exit;
}

echo "User: {$user->name} ({$user->phone})\n";

// Get trusted contacts
$contacts = \App\Models\TrustedContact::where('user_id', $user->id)
    ->where('active', true)
    ->get();

echo "Trusted contacts: " . $contacts->count() . "\n";

if ($contacts->isEmpty()) {
    echo "No trusted contacts found. Please add one first.\n";
    exit;
}

foreach ($contacts as $contact) {
    echo "  - {$contact->name}: {$contact->phone}\n";
}

// Create a safety incident
$incident = \App\Models\SafetyIncident::create([
    'user_id' => $user->id,
    'type' => 'missed_checkin',
    'status' => 'active',
    'message' => "{$user->name} missed their scheduled safety check-in.",
    'escalated_at' => now(),
]);

echo "✓ Created safety incident: {$incident->id}\n";

// Create notifications for each contact
foreach ($contacts as $contact) {
    $notification = \App\Models\IncidentNotification::create([
        'incident_id' => $incident->id,
        'trusted_contact_id' => $contact->id,
        'delivery_channel' => 'in_app',
        'status' => 'pending',
        'message' => "{$user->name} missed their safety check-in. Please check on them.",
    ]);
    echo "✓ Notification created for: {$contact->name} ({$contact->phone})\n";
}

// Log to activity
\App\Models\ActivityLog::create([
    'user_id' => $user->id,
    'type' => 'INCIDENT_CREATED',
    'status' => 'active',
    'details' => "Missed check-in incident created.",
    'occurred_at' => now(),
]);

echo "✅ Done!\n";
