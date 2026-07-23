<?php

namespace App\Listeners;

use App\Services\AutomationService;
use App\Services\RateLimiter;
use App\Services\QuietHoursResolver;
use App\Services\ChannelFallbackResolver;
use App\Services\NotificationDriverManager;
use Illuminate\Support\Facades\Log;

class EvaluateAutomationRules
{
    private AutomationService $automation;

    public function __construct()
    {
        $this->automation = new AutomationService(
            new RateLimiter(),
            new QuietHoursResolver(),
            new ChannelFallbackResolver(),
            app(NotificationDriverManager::class),
        );
    }

    public function handle(object $event): void
    {
        try {
            $eventType = $this->resolveEventType($event);
            $payload = $this->extractPayload($event);

            Log::info('[N9] Evaluating automation rules', [
                'event' => $eventType,
                'payload_keys' => array_keys($payload),
            ]);

            $this->automation->evaluate($eventType, $payload);
        } catch (\Exception $e) {
            Log::error('[N9] Automation evaluation failed', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveEventType(object $event): string
    {
        return match (get_class($event)) {
            \App\Events\CheckInCompleted::class => 'checkin_completed',
            default => strtolower(class_basename($event)),
        };
    }

    private function extractPayload(object $event): array
    {
        $payload = ['event_type' => $this->resolveEventType($event)];

        if (property_exists($event, 'incident') && $event->incident) {
            $incident = $event->incident;
            $payload['user_id'] = $incident->user_id ?? null;
            $payload['incident_id'] = $incident->id ?? null;
            $payload['incident_type'] = $incident->incident_type ?? null;
            $payload['latitude'] = $incident->latitude ?? null;
            $payload['longitude'] = $incident->longitude ?? null;
        }

        // CheckInCompleted
        if (property_exists($event, 'checkIn') && $event->checkIn) {
            $checkIn = $event->checkIn;
            $payload['user_id'] = $checkIn->user_id ?? $payload['user_id'] ?? null;
            $payload['checkin_id'] = $checkIn->id ?? null;
        }

        return $payload;
    }
}
