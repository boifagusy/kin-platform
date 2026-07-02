<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAlertRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'threshold',
        'time_window',
        'severity',
        'is_active',
        'actions',
    ];

    protected $casts = [
        'actions' => 'json',
        'is_active' => 'boolean',
    ];
}
