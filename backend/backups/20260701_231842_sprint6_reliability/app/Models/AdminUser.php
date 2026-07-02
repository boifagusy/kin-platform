<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(AdminLog::class, 'admin_user_id');
    }
}
