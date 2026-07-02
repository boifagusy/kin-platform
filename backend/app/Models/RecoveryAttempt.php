<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryAttempt extends Model
{
    protected $fillable = [
        'recovery_action_id', 'incident_id', 'subsystem', 'trigger',
        'status', 'message', 'data', 'started_at', 'finished_at',
        'duration_ms', 'escalated', 'escalation_reason', 'verification_result'
    ];
    
    protected $casts = [
        'data' => 'array',
        'verification_result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'escalated' => 'boolean'
    ];
    
    public function action()
    {
        return $this->belongsTo(RecoveryAction::class, 'recovery_action_id');
    }
    
    public function history()
    {
        return $this->hasMany(RecoveryHistory::class);
    }
}
