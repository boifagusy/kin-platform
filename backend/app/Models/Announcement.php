<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'message', 'type', 'priority', 'status',
        'starts_at', 'expires_at', 'target_platform',
        'min_version', 'max_version', 'dismissible',
    ];

    protected $hidden = ["deleted_at"];
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'dismissible' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->whereIn('target_platform', ['all', $platform]);
    }
}
