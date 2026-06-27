<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'reason',
        'notes',
        'suspended_by',
        'suspended_at',
        'reactivated_by',
        'reactivated_at',
    ];

    protected $casts = [
        'suspended_at' => 'datetime',
        'reactivated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function suspendedByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'suspended_by');
    }

    public function reactivatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reactivated_by');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' && $this->suspended_at !== null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
