<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyScore extends Model
{
    protected $table = 'safety_scores';

    protected $fillable = [
        'user_id',
        'score',
        'level',
        'factors',
        'calculated_at',
    ];

    protected $casts = [
        'factors' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
