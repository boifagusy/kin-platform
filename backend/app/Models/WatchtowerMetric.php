<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchtowerMetric extends Model
{
    protected $fillable = [
        'name',
        'category',
        'value_type',
        'value',
        'labels',
        'collected_at',
    ];

    protected $casts = [
        'labels' => 'json',
        'collected_at' => 'datetime',
    ];
}
