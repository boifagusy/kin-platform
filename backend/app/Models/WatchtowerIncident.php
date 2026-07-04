<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchtowerIncident extends Model
{
    protected $table = 'watchtower_incidents';

    protected $fillable = [
        'title',
        'description',
        'severity',
        'status',
        'source',
        'source_id',
        'user_id',
        'metadata',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
