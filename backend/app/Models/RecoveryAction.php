<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryAction extends Model
{
    protected $table = 'recovery_actions';

    protected $fillable = [
        'name',
        'class',
        'description',
        'is_safe',
        'is_rollbackable',
        'config',
    ];

    protected $casts = [
        'is_safe' => 'boolean',
        'is_rollbackable' => 'boolean',
        'config' => 'array',
    ];

    public function attempts()
    {
        return $this->hasMany(RecoveryAttempt::class);
    }
}
