<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyIncident extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'location_lat',
        'location_lng',
        'location_accuracy',
        'battery_level',
        'message',
        'resolved_at',
        'escalated_at',
    ];

    protected $casts = [
        'location_lat' => 'float',
        'location_lng' => 'float',
        'location_accuracy' => 'integer',
        'battery_level' => 'integer',
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(IncidentNotification::class);
    }
}
