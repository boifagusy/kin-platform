<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchtowerAlertRule extends Model
{
    protected $fillable = [
        'name',
        'metric_type',
        'condition', // >, <, ==, !=
        'threshold',
        'duration',
        'severity', // info, warning, critical
        'action',
        'enabled',
        'notify_channels',
        'notify_roles',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'notify_channels' => 'json',
        'notify_roles' => 'json',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
