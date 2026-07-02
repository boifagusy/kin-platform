<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchtowerAlertRule extends Model
{
    protected $fillable = [
        'name',
        'type',
        'target',
        'metric',
        'operator',
        'threshold',
        'severity',
        'cooldown_seconds',
        'deduplication_window',
        'channels',
        'escalation_config',
        'is_active',
        'maintenance_until',
    ];

    protected $casts = [
        'channels' => 'json',
        'escalation_config' => 'json',
        'maintenance_until' => 'datetime',
        'is_active' => 'boolean',
    ];
}
