<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCheckInReminderJob implements ShouldQueue
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
            Log::error('SendCheckInReminderJob: User not found', [
                'user_id' => $this->userId
            ]);
            return;
        }

        // Log reminder sent
        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'CHECKIN_REMINDER_SENT',
            'status' => 'sent',
            'details' => 'Check-in reminder sent to user',
            'occurred_at' => now(),
        ]);

        Log::info('Check-in reminder processed via queue', [
            'user_id' => $user->id
        ]);
    }
}
