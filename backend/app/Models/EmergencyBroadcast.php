<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyBroadcast extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'message', 'severity', 'audience_id', 'expires_at', 'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function audience()
    {
        return $this->belongsTo(Audience::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
