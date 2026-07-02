<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceTrust extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'trust_score',
        'root_detected',
        'emulator_detected',
        'sim_changed',
        'app_reinstalled',
        'last_checked_at',
        'reasons',
    ];

    protected $casts = [
        'root_detected' => 'boolean',
        'emulator_detected' => 'boolean',
        'sim_changed' => 'boolean',
        'app_reinstalled' => 'boolean',
        'trust_score' => 'integer',
        'reasons' => 'array',
        'last_checked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if device is trusted
     */
    public function isTrusted(): bool
    {
        return $this->trust_score >= 70;
    }

    /**
     * Get trust level label
     */
    public function getTrustLevel(): string
    {
        if ($this->trust_score >= 80) return 'high';
        if ($this->trust_score >= 50) return 'medium';
        return 'low';
    }
}
