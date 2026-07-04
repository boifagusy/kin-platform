<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryPolicy extends Model
{
    protected $table = 'recovery_policies';

    protected $fillable = [
        'name',
        'trigger_condition',
        'actions',
        'max_attempts',
        'retry_delay_seconds',
        'escalate_on_failure',
        'escalation_level',
        'is_active',
        'config',
    ];

    protected $casts = [
        'actions' => 'array',
        'config' => 'array',
        'escalate_on_failure' => 'boolean',
        'is_active' => 'boolean',
    ];
}
