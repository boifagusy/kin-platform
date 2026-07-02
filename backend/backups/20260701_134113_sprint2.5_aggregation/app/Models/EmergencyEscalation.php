<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmergencyEscalation extends Model
{
    use HasFactory;

    // Escalation Levels
    const LEVEL_ORANGE = 'orange';
    const LEVEL_RED = 'red';
    const LEVEL_BLACK = 'black';

    // Escalation Status
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_ESCALATED = 'escalated';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'safety_incident_id',
        'escalation_type',
        'level',
        'status',
        'priority',
        'confidence_score',
        'location_lat',
        'location_lng',
        'location_accuracy',
        'battery_level',
        'assigned_admin_id',
        'resolved_by',
        'escalated_at',
        'timeout_at',
        'retry_count',
        'reason',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'escalated_at' => 'datetime',
        'timeout_at' => 'datetime',
        'resolved_at' => 'datetime',
        'confidence_score' => 'integer',
        'retry_count' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function safetyIncident()
    {
        return $this->belongsTo(SafetyIncident::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'assigned_admin_id');
    }

    public function resolver()
    {
        return $this->belongsTo(AdminUser::class, 'resolved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByConfidence($query, $minScore)
    {
        return $query->where('confidence_score', '>=', $minScore);
    }

    // Methods
    public function escalate()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->escalated_at = now();
        $this->save();

        // Determine timeout based on level
        $timeoutMinutes = match($this->level) {
            self::LEVEL_ORANGE => 60,
            self::LEVEL_RED => 30,
            self::LEVEL_BLACK => 15,
            default => 60
        };

        $this->timeout_at = now()->addMinutes($timeoutMinutes);
        $this->save();

        return $this;
    }

    public function resolve($adminId = null)
    {
        $this->status = self::STATUS_RESOLVED;
        $this->resolved_at = now();
        if ($adminId) {
            $this->resolved_by = $adminId;
        }
        $this->save();

        return $this;
    }

    public function expire()
    {
        $this->status = self::STATUS_EXPIRED;
        $this->save();

        return $this;
    }

    public function incrementRetry()
    {
        $this->retry_count += 1;
        $this->save();

        return $this->retry_count;
    }

    public function isTimeout(): bool
    {
        if (!$this->timeout_at) {
            return false;
        }
        return now()->gt($this->timeout_at);
    }

    public function getLevelLabel(): string
    {
        return match($this->level) {
            self::LEVEL_ORANGE => '🟠 Orange',
            self::LEVEL_RED => '🔴 Red',
            self::LEVEL_BLACK => '⚫ Black',
            default => 'Unknown'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '⏳ Pending',
            self::STATUS_ACTIVE => '🔄 Active',
            self::STATUS_ESCALATED => '📤 Escalated',
            self::STATUS_RESOLVED => '✅ Resolved',
            self::STATUS_EXPIRED => '⏰ Expired',
            default => 'Unknown'
        };
    }

    public function getTimeRemaining(): ?string
    {
        if (!$this->timeout_at) {
            return null;
        }

        $diff = now()->diff($this->timeout_at);
        if ($diff->invert) {
            return 'Expired';
        }

        return $diff->format('%Hh %Im %Ss');
    }

    public function needsEscalation(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->isTimeout();
    }
}
