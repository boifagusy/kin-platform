<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Audience extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'platform', 'min_version_code', 'max_version_code', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->whereIn('platform', [$platform, 'all']);
    }

    public function matchesClient(string $platform, ?int $versionCode): bool
    {
        if (!in_array($this->platform, [$platform, 'all'])) {
            return false;
        }
        if ($this->min_version_code && $versionCode && $versionCode < $this->min_version_code) {
            return false;
        }
        if ($this->max_version_code && $versionCode && $versionCode > $this->max_version_code) {
            return false;
        }
        return true;
    }
}
