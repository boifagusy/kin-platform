<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_user_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'details' => 'json',
    ];

    // Relationship to admin user
    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    // Alias for compatibility (if needed)
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
