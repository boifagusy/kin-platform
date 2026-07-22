<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\EmergencyEscalation;
use App\Models\IncidentNotification;
use App\Models\SafetyIncident;
use App\Models\TrustedContact;
use App\Services\SafetyScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSosAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 300];

    protected $user;
    protected $location;
    protected $isDuress;
    protected $confidence;

    public function __construct(User $user, array $location = null, bool $isDuress = false)
    {
        $this->user = $user;
        $this->location = $location;
        $this->isDuress = $isDuress;
        
        // Get confidence score
        $scoreService = app(SafetyScoreService::class);
        $this->confidence = $scoreService->getForUser($user);
    }

    public function handle()
    {
        try {
            // Determine escalation level based on duress and confidence
            $level = $this->determineEscalationLevel();
            
            // Create safety incident via EmergencyLifecycleService
            $incident = app(\App\Services\EmergencyLifecycleService::class)->trigger($this->user->id, [
                'type' => $this->isDuress ? 'duress' : 'sos_triggered',
                'message' => $this->isDuress ? 'Duress SOS triggered' : 'SOS triggered',
                'location_lat' => $this->location['lat'] ?? null,
                'location_lng' => $this->location['lng'] ?? null,
                'silent' => false,
            ]);

            // Create escalation
            $escalation = EmergencyEscalation::create([
                'user_id' => $this->user->id,
                'safety_incident_id' => $incident->id,
                'escalation_type' => 'sos',
                'level' => $level,
                'status' => EmergencyEscalation::STATUS_PENDING,
                'priority' => $this->getPriority($level),
                'confidence_score' => $this->confidence,
                'location_lat' => $this->location['lat'] ?? null,
                'location_lng' => $this->location['lng'] ?? null,
                'reason' => $this->isDuress ? 'Duress signal detected' : 'SOS triggered by user',
            ]);

            // Start escalation
            $escalation->escalate();

            // Get trusted contacts
            $contacts = TrustedContact::where('user_id', $this->user->id)
                ->where('verified', true)
                ->get();

            if ($contacts->isEmpty()) {
                Log::warning('No trusted contacts found for SOS', ['user_id' => $this->user->id]);
            }

            // Send notifications to each contact
            foreach ($contacts as $contact) {
                $notification = IncidentNotification::create([
                    'safety_incident_id' => $incident->id,
                    'contact_id' => $contact->id,
                    'type' => 'sos',
                    'status' => 'pending',
                    'delivery_method' => 'sms',
                ]);

                // Dispatch notification job
                dispatch(new SendEscalationNotificationJob($this->user->id, $level));
            }

            // If duress, also trigger check escalation job
            if ($this->isDuress) {
                dispatch(new CheckEscalationJob($this->user->id));
            }

            Log::info('SOS alert processed', [
                'user_id' => $this->user->id,
                'level' => $level,
                'is_duress' => $this->isDuress,
                'confidence' => $this->confidence,
                'contacts' => $contacts->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process SOS alert', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function determineEscalationLevel(): string
    {
        // If duress, always escalate to at least Red
        if ($this->isDuress) {
            return EmergencyEscalation::LEVEL_RED;
        }

        // Based on confidence score
        if ($this->confidence < 20) {
            return EmergencyEscalation::LEVEL_BLACK;
        } elseif ($this->confidence < 40) {
            return EmergencyEscalation::LEVEL_RED;
        } elseif ($this->confidence < 60) {
            return EmergencyEscalation::LEVEL_ORANGE;
        }

        // Default to Orange for SOS
        return EmergencyEscalation::LEVEL_ORANGE;
    }

    private function getPriority(string $level): int
    {
        return match($level) {
            EmergencyEscalation::LEVEL_BLACK => 1,
            EmergencyEscalation::LEVEL_RED => 2,
            EmergencyEscalation::LEVEL_ORANGE => 3,
            default => 4
        };
    }
}
