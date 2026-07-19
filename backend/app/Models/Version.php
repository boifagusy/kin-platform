<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Version extends Model
{
    use SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_REVIEW = 'review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'version_code', 'version_name', 'release_notes',
        'force_update', 'is_active', 'release_date', 'min_version_code',
        'status', 'approved_by', 'reviewed_at',
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'is_active' => 'boolean',
        'release_date' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function channels()
    {
        return $this->hasMany(VersionChannel::class);
    }

    public function approver()
    {
        return $this->belongsTo(AdminUser::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where("is_active", true)->whereNull("deleted_at");
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
