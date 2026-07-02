<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchtowerRunbook extends Model
{
    protected $fillable = [
        'title',
        'trigger_condition',
        'description',
        'impact',
        'recommended_actions',
        'estimated_recovery_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
