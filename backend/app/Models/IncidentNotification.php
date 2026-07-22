<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id', 'trusted_contact_id',
        'delivery_channel', 'status', 'message',
        'delivered_at', 'viewed_at', 'silent',
        'registry_key', 'trigger', 'priority',
        'action_required', 'action_completed', 'action_data',
        'storage_policy', 'sync_status', 'expires_at', 'lifecycle_state',
    ];

    protected $casts = [
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
