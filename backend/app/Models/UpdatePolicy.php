<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpdatePolicy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'version_id', 'platform', 'policy', 'priority',
        'grace_days', 'reason', 'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'grace_days' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function version()
    {
        return $this->belongsTo(Version::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNull('deleted_at');
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->whereIn('platform', [$platform, 'all']);
    }

    public function scopeInWindow($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
