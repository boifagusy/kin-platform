<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentNotification extends Model
{
    protected $fillable = [
        'incident_id',
        'trusted_contact_id',
        'delivery_channel',
        'status',
        'delivered_at',
        'viewed_at',
        'message',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(SafetyIncident::class);
    }

    public function trustedContact()
    {
        return $this->belongsTo(TrustedContact::class);
    }
}
