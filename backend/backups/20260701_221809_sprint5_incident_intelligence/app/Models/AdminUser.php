<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $table = 'admin_users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'last_login_at',
        'last_login_ip',
        'is_active',
        'created_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}
