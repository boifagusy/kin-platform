<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'provider',
        'battery_level',
        'is_background',
        'tracked_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'float',
        'speed' => 'float',
        'heading' => 'float',
        'battery_level' => 'integer',
        'is_background' => 'boolean',
        'tracked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get location as array
     */
    public function toLocationArray(): array
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'accuracy' => $this->accuracy,
            'provider' => $this->provider,
            'timestamp' => $this->tracked_at->toISOString(),
        ];
    }

    /**
     * Check if location is fresh (within last 5 minutes)
     */
    public function isFresh(): bool
    {
        return $this->tracked_at->diffInMinutes(now()) < 5;
    }
}
