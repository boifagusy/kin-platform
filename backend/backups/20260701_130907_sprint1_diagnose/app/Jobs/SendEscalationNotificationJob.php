<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\TrustedContact;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEscalationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $level;

    public function __construct(int $userId, string $level)
    {
        $this->userId = $userId;
        $this->level = $level;
    }

    public function handle(NotificationService $notificationService): void
    {
        $user = User::find($this->userId);
        if (!$user) return;

        $contacts = TrustedContact::where('user_id', $user->id)
            ->where('verified', true)
            ->get();

        $message = $this->getMessage($this->level, $user);

        foreach ($contacts as $contact) {
            $notificationService->sendSms(
                $contact->phone,
                $message
            );
            
            if ($contact->email) {
                $notificationService->sendEmail(
                    $contact->email,
                    "Safety Alert: {$this->level}",
                    $message
                );
            }
        }

        Log::info('Escalation notification sent', [
            'user_id' => $user->id,
            'level' => $this->level,
            'contacts' => $contacts->count()
        ]);
    }

    private function getMessage(string $level, User $user): string
    {
        $messages = [
            'orange' => "⚠️ Safety Alert: We've noticed some concerning activity. Please check on {$user->name}.",
            'red' => "🚨 URGENT: {$user->name} requires immediate attention. Please contact them now.",
            'black' => "🆘 EMERGENCY: Immediate action required for {$user->name}. Please contact authorities.",
        ];

        return $messages[$level] ?? "Safety notification for {$user->name}.";
    }
}
