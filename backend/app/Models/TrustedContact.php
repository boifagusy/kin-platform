<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustedContact extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone',
        'status', 'token_hash', 'token_expires_at',
        'resend_count', 'verified_at', 'revoked_at',
        'verification_method',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $appends = ['verified'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Derived field: verified = (status === 'accepted')
     * For backward compatibility only. Do not use for business logic.
     */
    public function getVerifiedAttribute(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Scope: Verified contacts only (accepted status)
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: Active requests/invitations (not yet accepted/declined/removed)
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending_request', 'pending_invitation']);
    }

    /**
     * Scope: For a specific user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope: Not removed, declined, or cancelled
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['removed', 'declined', 'cancelled', 'expired']);
    }
}
