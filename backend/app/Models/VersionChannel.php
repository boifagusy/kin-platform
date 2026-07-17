<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VersionChannel extends Model
{
    protected $fillable = [
        'version_id', 'platform', 'channel', 'download_url', 'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function version()
    {
        return $this->belongsTo(Version::class);
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', $platform)->where('enabled', true);
    }
}
