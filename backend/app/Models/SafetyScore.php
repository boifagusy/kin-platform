<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyScore extends Model
{
    protected $fillable = [
        'user_id', 'score', 'level', 'factors', 'metadata', 'calculated_at'
    ];
    
    protected $casts = [
        'factors' => 'array',
        'metadata' => 'array',
        'calculated_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
