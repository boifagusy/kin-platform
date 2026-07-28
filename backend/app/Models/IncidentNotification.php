<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'incident_id', 'trusted_contact_id',
        'type', 'category', 'title', 'body', 'message',
        'action_url', 'metadata',
        'delivery_channel', 'status',
        'read', 'read_at', 'resolved_at', 'delivered_at', 'viewed_at',
        'silent',
        'registry_key', 'trigger', 'priority',
        'action_required', 'action_completed', 'action_data',
        'storage_policy', 'sync_status', 'expires_at', 'lifecycle_state',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
        'delivered_at' => 'datetime',
        'viewed_at' => 'datetime',
        'expires_at' => 'datetime',
        'silent' => 'boolean',
        'action_required' => 'boolean',
        'action_completed' => 'boolean',
        'action_data' => 'array',
    ];

    public function incident()
    {
        return $this->belongsTo(SafetyIncident::class, 'incident_id');
    }
}
