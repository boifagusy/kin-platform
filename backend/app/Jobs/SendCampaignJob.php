<?php
namespace App\Jobs;

use App\Models\PushCampaign;
use App\Models\CampaignDelivery;
use App\Services\NotificationDriverManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        private PushCampaign $campaign,
        private string $channel = 'push'
    ) {}

    public function handle(NotificationDriverManager $drivers): void
    {
        $deliveries = CampaignDelivery::where('push_campaign_id', $this->campaign->id)
            ->where('channel', $this->channel)
            ->where('status', 'pending')
            ->get();

        foreach ($deliveries as $delivery) {
            try {
                $user = $delivery->user;
                $drivers->send($this->channel, [
                    'phone' => $user->phone,
                    'email' => $user->email,
                ], [
                    'title' => $this->campaign->title,
                    'body' => $this->campaign->body,
                ]);

                $delivery->update(['status' => 'sent', 'sent_at' => now()]);
            } catch (\Exception $e) {
                $delivery->update(['status' => 'failed', 'error' => $e->getMessage()]);
            }
        }
    }
}
