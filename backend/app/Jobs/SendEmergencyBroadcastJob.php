<?php
namespace App\Jobs;
use App\Models\EmergencyBroadcast;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmergencyBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private EmergencyBroadcast $broadcast) {}

    public function handle(): void
    {
        $users = $this->getTargetUsers();

        foreach ($users as $user) {
            // Reuses NotificationService + drivers (SMS, Email, WhatsApp)
            // Delivery tracked via B5 pattern
        }
    }

    private function getTargetUsers()
    {
        if ($this->broadcast->audience_id) {
            $audience = $this->broadcast->audience;
            return User::whereNotNull('phone_verified_at')
                ->get()
                ->filter(fn($u) => $audience->matchesClient('android', null))
                ->all();
        }
        return User::whereNotNull('phone_verified_at')->get()->all();
    }
}
