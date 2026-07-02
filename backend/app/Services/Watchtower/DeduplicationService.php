<?php

namespace App\Services\Watchtower;

use App\Models\WatchtowerIncident;
use Illuminate\Support\Facades\Cache;

class DeduplicationService
{
    protected $deduplicationWindow = 3600; // 1 hour default

    public function shouldCreateIncident(string $source, string $title, array $metadata = []): bool
    {
        $key = $this->getDeduplicationKey($source, $title);
        
        // Check if we have a recent incident for this source/title
        $existing = Cache::get($key);
        
        if ($existing) {
            // Increment count and update last seen
            $existing['count']++;
            $existing['last_seen'] = now()->toISOString();
            Cache::put($key, $existing, $this->deduplicationWindow);
            
            // Update the incident with the new count
            $this->updateIncidentCount($existing['incident_id'], $existing['count']);
            
            return false;
        }
        
        // Create a new incident
        Cache::put($key, [
            'incident_id' => null, // Will be set after creation
            'count' => 1,
            'first_seen' => now()->toISOString(),
            'last_seen' => now()->toISOString(),
        ], $this->deduplicationWindow);
        
        return true;
    }

    public function recordIncident(WatchtowerIncident $incident)
    {
        $key = $this->getDeduplicationKey($incident->source, $incident->title);
        $data = Cache::get($key);
        
        if ($data) {
            $data['incident_id'] = $incident->id;
            Cache::put($key, $data, $this->deduplicationWindow);
        }
    }

    public function getDeduplicationKey(string $source, string $title): string
    {
        return 'watchtower_dedup_' . md5($source . '_' . $title);
    }

    protected function updateIncidentCount(int $incidentId, int $count)
    {
        $incident = WatchtowerIncident::find($incidentId);
        if ($incident) {
            $metadata = $incident->metadata ?? [];
            $metadata['occurrence_count'] = $count;
            $metadata['last_occurrence'] = now()->toISOString();
            $incident->update(['metadata' => $metadata]);
        }
    }
}
