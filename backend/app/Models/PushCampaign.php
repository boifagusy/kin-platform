<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PushCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'body', 'status', 'scheduled_at', 'sent_at', 'audience_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function audience()
    {
        return $this->belongsTo(Audience::class);
    }

    public function deliveries()
    {
        return $this->hasMany(CampaignDelivery::class);
    }
}
