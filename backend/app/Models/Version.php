<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Version extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'version_code', 'version_name', 'release_notes',
        'force_update', 'is_active', 'release_date', 'min_version_code',
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'is_active' => 'boolean',
        'release_date' => 'datetime',
    ];

    public function channels()
    {
        return $this->hasMany(VersionChannel::class);
    }

    public function scopeActive($query)
    {
        return $query->where("is_active", true)->whereNull("deleted_at");
    }
}
