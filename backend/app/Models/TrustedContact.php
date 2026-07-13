<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustedContact extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone', 'verified', 'active',
        'status', 'token_hash', 'token_expires_at',
        'resend_count', 'verified_at', 'revoked_at',
        'verification_method',
    ];

    protected $casts = [
        'active' => 'boolean',
        'token_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $appends = ['is_verified'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->status === 'verified';
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
