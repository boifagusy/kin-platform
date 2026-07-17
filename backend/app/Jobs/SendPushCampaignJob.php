<?php
namespace App\Jobs;
use App\Models\PushCampaign;
use App\Models\CampaignDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private PushCampaign $campaign) {}

    public function handle(): void
    {
        $deliveries = CampaignDelivery::where('push_campaign_id', $this->campaign->id)
            ->where('status', 'pending')
            ->get();

        foreach ($deliveries as $delivery) {
            try {
                // FCM/APNs integration point — currently logs
                $delivery->update(['status' => 'sent', 'sent_at' => now()]);
            } catch (\Exception $e) {
                $delivery->update(['status' => 'failed', 'error' => $e->getMessage()]);
            }
        }
    }
}
