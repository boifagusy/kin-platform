<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyEvent extends Model
{
    protected $table = 'safety_events';

    protected $fillable = [
        'user_id',
        'event_type',
        'description',
        'severity',
        'location',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
