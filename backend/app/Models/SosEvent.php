<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosEvent extends Model
{
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'triggered_at',
        'resolved_at',
        'is_duress',
        'accuracy',
        'battery_level',
        'safety_incident_id',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_duress' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function safetyIncident(): BelongsTo
    {
        return $this->belongsTo(SafetyIncident::class);
    }
}
