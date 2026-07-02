<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchtowerIncident extends Model
{
    protected $fillable = [
        'title',
        'description',
        'severity', // critical, high, medium, low, info
        'status', // new, investigating, acknowledged, mitigated, resolved, closed
        'source', // api, queue, database, storage, plugin, safety, security
        'affected_services',
        'metadata',
        'root_cause',
        'resolution_notes',
        'detected_at',
        'acknowledged_at',
        'investigating_at',
        'mitigated_at',
        'resolved_at',
        'closed_at',
        'assigned_to',
        'resolved_by',
        'recovery_duration_seconds',
        'is_escalated',
        'escalation_history',
    ];

    protected $casts = [
        'affected_services' => 'json',
        'metadata' => 'json',
        'escalation_history' => 'json',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'investigating_at' => 'datetime',
        'mitigated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_escalated' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'new',
        'severity' => 'medium',
    ];

    // Status transitions
    public function acknowledge(): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);
    }

    public function startInvestigating(): void
    {
        $this->update([
            'status' => 'investigating',
            'investigating_at' => now(),
        ]);
    }

    public function mitigate(): void
    {
        $this->update([
            'status' => 'mitigated',
            'mitigated_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'recovery_duration_seconds' => $this->detected_at ? $this->detected_at->diffInSeconds(now()) : null,
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }
}
