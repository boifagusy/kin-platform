<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\EmergencyEscalation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMissedCheckInJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::error('ProcessMissedCheckInJob: User not found', [
                'user_id' => $this->userId
            ]);
            return;
        }

        // Log missed check-in
        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'CHECKIN_MISSED',
            'status' => 'active',
            'details' => 'User missed scheduled check-in',
            'occurred_at' => now(),
        ]);

        // Create escalation
        EmergencyEscalation::create([
            'user_id' => $user->id,
            'escalation_type' => 'missed_checkin',
            'status' => 'active',
            'priority' => 'high',
            'notes' => 'Missed check-in escalation triggered via queue',
        ]);

        Log::info('Missed check-in processed via queue', [
            'user_id' => $user->id
        ]);
    }
}
