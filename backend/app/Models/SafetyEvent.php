<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyEvent extends Model
{
    protected $table = 'safety_events';

    protected $fillable = [
        'user_id',
        'event_type',
        'correlation_id',
        'location_lat',
        'location_lng',
        'location_confidence',
        'battery_level',
        'network_status',
        'device_status',
        'trusted_contacts_notified',
        'guardian_acknowledged_at',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'location_confidence' => 'integer',
        'battery_level' => 'integer',
        'trusted_contacts_notified' => 'boolean',
        'guardian_acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->resolved_at === null;
    }

    public function isGuardianAcknowledged(): bool
    {
        return $this->guardian_acknowledged_at !== null;
    }

    public function resolve(): void
    {
        $this->update(['resolved_at' => now()]);
    }

    public function getConfidenceLevel(): string
    {
        if ($this->location_confidence >= 80) {
            return 'High';
        }
        if ($this->location_confidence >= 50) {
            return 'Medium';
        }
        return 'Low';
    }
}
