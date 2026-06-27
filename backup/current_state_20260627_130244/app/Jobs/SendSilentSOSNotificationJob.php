<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SafetyIncident;
use App\Models\TrustedContact;
use App\Models\IncidentNotification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSilentSOSNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $incident;
    public $tries = 5;
    public $backoff = [60, 120, 180, 300, 600];

    public function __construct(User $user, SafetyIncident $incident)
    {
        $this->user = $user;
        $this->incident = $incident;
    }

    public function handle(NotificationService $notificationService)
    {
        $contacts = TrustedContact::where('user_id', $this->user->id)
            ->where('verified', true)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($contacts->isEmpty()) {
            Log::warning('No trusted contacts for silent SOS', [
                'user_id' => $this->user->id
            ]);
            return;
        }

        // Get disguised message
        $message = $this->getDisguisedMessage();
        $emailSubject = $this->getDisguisedSubject();

        $attempts = 0;
        $maxAttempts = 3;
        $success = false;

        while ($attempts < $maxAttempts && !$success) {
            try {
                foreach ($contacts as $contact) {
                    // Send SMS with disguised message
                    $notificationService->sendSms($contact->phone, $message);

                    // Send email with disguised subject
                    if ($contact->email) {
                        $notificationService->sendEmail(
                            $contact->email,
                            $emailSubject,
                            $message
                        );
                    }

                    // Create notification record
                    IncidentNotification::create([
                        'safety_incident_id' => $this->incident->id,
                        'contact_id' => $contact->id,
                        'type' => 'sos',
                        'status' => 'sent',
                        'delivery_method' => 'sms',
                        'silent' => true,
                    ]);

                    $success = true;
                }
            } catch (\Exception $e) {
                $attempts++;
                Log::warning("Silent SOS notification attempt {$attempts} failed", [
                    'error' => $e->getMessage(),
                    'user_id' => $this->user->id,
                ]);

                if ($attempts < $maxAttempts) {
                    sleep(pow(2, $attempts)); // Exponential backoff
                }
            }
        }

        if (!$success) {
            // Store for manual review
            $this->createAlertForSupport($this->user, $this->incident);
            Log::error('Silent SOS notifications failed after retries', [
                'user_id' => $this->user->id,
            ]);
        }

        Log::info('Silent SOS notifications processed', [
            'user_id' => $this->user->id,
            'contacts' => $contacts->count(),
            'success' => $success,
            'silent' => true,
        ]);
    }

    private function getDisguisedMessage(): string
    {
        $messages = [
            "Reminder: Your KIN check-in is due soon. If you're safe, no action needed.",
            "KIN Safety: Don't forget your daily check-in today. Stay safe!",
            "Check-in reminder from KIN: How are you doing today?",
            "KIN Safety: Weather update for your area. Stay safe!",
            "Your KIN daily update is ready. Review your safety status.",
        ];

        return $messages[array_rand($messages)];
    }

    private function getDisguisedSubject(): string
    {
        $subjects = [
            "KIN Daily Update",
            "Your KIN Check-in Reminder",
            "KIN Safety Update",
            "KIN: Weather Alert for Your Area",
            "KIN: Daily Safety Report",
        ];

        return $subjects[array_rand($subjects)];
    }

    private function createAlertForSupport(User $user, SafetyIncident $incident): void
    {
        // Create an admin alert for manual follow-up
        \App\Models\AlertNote::create([
            'user_id' => $user->id,
            'safety_incident_id' => $incident->id,
            'type' => 'silent_sos_failure',
            'status' => 'pending',
            'message' => 'Silent SOS notifications failed after multiple retries. Manual intervention required.',
            'created_at' => now(),
        ]);
    }
}
