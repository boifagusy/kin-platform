<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryHistory extends Model
{
    protected $table = 'recovery_history';
    
    protected $fillable = [
        'recovery_attempt_id', 'event_type', 'message', 'context'
    ];
    
    protected $casts = [
        'context' => 'array'
    ];
    
    public function attempt()
    {
        return $this->belongsTo(RecoveryAttempt::class, 'recovery_attempt_id');
    }
}
