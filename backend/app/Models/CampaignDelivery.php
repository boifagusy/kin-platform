<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CampaignDelivery extends Model
{
    protected $fillable = [
        'push_campaign_id', 'user_id', 'status', 'sent_at', 'error', 'channel',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(PushCampaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
