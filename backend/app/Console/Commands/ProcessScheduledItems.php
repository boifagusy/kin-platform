<?php
namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\PushCampaign;
use App\Models\EmergencyBroadcast;
use App\Models\UpdatePolicy;
use Illuminate\Console\Command;

class ProcessScheduledItems extends Command
{
    protected $signature = 'kin:process-scheduled';
    protected $description = 'Process all time-based state transitions';

    public function handle()
    {
        // Activate announcements past their start time
        $announcements = Announcement::where('status', 'scheduled')
            ->where('starts_at', '<=', now())
            ->get();
        foreach ($announcements as $a) {
            $a->update(['status' => 'published']);
            $this->info("Activated announcement: {$a->title}");
        }

        // Expire announcements past their expiry
        $expired = Announcement::where('status', 'published')
            ->where('expires_at', '<=', now())
            ->get();
        foreach ($expired as $a) {
            $a->update(['status' => 'expired']);
            $this->info("Expired announcement: {$a->title}");
        }

        // Activate scheduled push campaigns
        $campaigns = PushCampaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();
        foreach ($campaigns as $c) {
            $c->update(['status' => 'sending']);
            \App\Jobs\SendPushCampaignJob::dispatch($c);
            $this->info("Activated campaign: {$c->title}");
        }

        // Expire emergency broadcasts
        $broadcasts = EmergencyBroadcast::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->get();
        foreach ($broadcasts as $b) {
            $b->update(['status' => 'completed']);
            $this->info("Expired broadcast: {$b->title}");
        }

        // Activate/deactivate policies based on time window
        $policies = UpdatePolicy::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now())
            ->get();

        $this->info('Scheduled items processed: ' . 
            $announcements->count() . ' announcements, ' .
            $campaigns->count() . ' campaigns, ' .
            $broadcasts->count() . ' broadcasts.');
        
        return 0;
    }
}
