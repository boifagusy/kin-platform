<?php

namespace App\Services\Recovery;

use App\Models\RecoveryAttempt;
use App\Models\RecoveryHistory;

class RecoveryHistoryService
{
    public function log(RecoveryAttempt $attempt, string $eventType, string $message, array $context = []): void
    {
        RecoveryHistory::create([
            'recovery_attempt_id' => $attempt->id,
            'event_type' => $eventType,
            'message' => $message,
            'context' => $context
        ]);
    }
    
    public function getHistory(RecoveryAttempt $attempt): \Illuminate\Support\Collection
    {
        return RecoveryHistory::where('recovery_attempt_id', $attempt->id)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
