<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchtowerIncident extends Model
{
    protected $fillable = [
        'incident_type',
        'severity', // info, warning, critical
        'title',
        'description',
        'service',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'resolved_at' => 'datetime',
    ];

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function resolve(): void
    {
        $this->update(['resolved_at' => now()]);
    }
}
