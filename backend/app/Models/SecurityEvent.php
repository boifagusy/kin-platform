<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SanitizesInput;

class SecurityEvent extends Model
{
    use SanitizesInput;

    protected $fillable = [
        'event_type',
        'severity',
        'source_ip',
        'user_agent',
        'user_id',
        'details',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'details' => 'json',
        'resolved_at' => 'datetime',
    ];

    protected $with = ['user']; // Eager load user by default

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes for common queries
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeFailedLogins($query)
    {
        return $query->where('event_type', 'login_pin_failed');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeInTimeWindow($query, $seconds)
    {
        return $query->where('created_at', '>=', now()->subSeconds($seconds));
    }
}
