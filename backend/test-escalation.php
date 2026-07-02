<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('phone', 'like', '%234%')->first();
if ($user) {
    echo "Found user: {$user->name} ({$user->phone})\n";
    
    $contacts = \App\Models\TrustedContact::where('user_id', $user->id)
        ->where('active', true)
        ->get();
    
    echo "Found " . $contacts->count() . " trusted contacts\n";
    
    foreach ($contacts as $contact) {
        echo "  - {$contact->contact_name}: {$contact->contact_phone}\n";
    }
    
    // Create a test incident
    $incident = \App\Models\SafetyIncident::create([
        'user_id' => $user->id,
        'type' => 'test',
        'status' => 'active',
        'message' => 'Test incident from escalation flow',
        'escalated_at' => now(),
    ]);
    echo "✓ Created test incident: {$incident->id}\n";
} else {
    echo "No user found\n";
}
