<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'login_pin_hash',
        'duress_pin_hash',
        'last_checkin_at',
        'last_location',
        'onboarding_completed',
        'onboarding_step',
        'onboarding_draft',
        'last_login_at',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'login_pin_hash',
        'duress_pin_hash',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_checkin_at' => 'datetime',
        'last_login_at' => 'datetime',
        'onboarding_completed' => 'boolean',
        'onboarding_draft' => 'array',
        'status' => 'string',
        'deleted_at' => 'datetime',
    ];

    // Renamed from status() to avoid conflict with column
    public function userStatus()
    {
        return $this->hasOne(UserStatus::class)->latestOfMany();
    }

    public function statusHistory()
    {
        return $this->hasMany(UserStatus::class)->orderBy('created_at', 'desc');
    }

    // Fixed to use the status column directly
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function trustedContacts()
    {
        return $this->hasMany(TrustedContact::class);
    }

    public function sosEvents()
    {
        return $this->hasMany(SosEvent::class);
    }
}
