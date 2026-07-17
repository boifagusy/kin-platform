<?php
namespace App\Services;
use App\Models\PushCampaign;
use App\Models\CampaignDelivery;
use App\Models\User;
use App\Jobs\SendPushCampaignJob;

class PushCampaignService
{
    public function create(array $data): PushCampaign
    {
        return PushCampaign::create($data);
    }

    public function update(PushCampaign $campaign, array $data): PushCampaign
    {
        $campaign->update($data);
        return $campaign;
    }

    public function delete(PushCampaign $campaign): void
    {
        $campaign->delete();
    }

    public function schedule(PushCampaign $campaign): PushCampaign
    {
        $campaign->update(['status' => 'scheduled']);
        return $campaign;
    }

    public function send(PushCampaign $campaign): void
    {
        $campaign->update(['status' => 'sending']);

        $users = $this->getTargetUsers($campaign);

        foreach ($users as $user) {
            CampaignDelivery::create([
                'push_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }

        SendPushCampaignJob::dispatch($campaign);

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);
    }

    private function getTargetUsers(PushCampaign $campaign): array
    {
        if ($campaign->audience_id) {
            $audience = $campaign->audience;
            return User::whereNotNull('phone_verified_at')
                ->get()
                ->filter(fn($u) => $audience->matchesClient('android', null))
                ->all();
        }

        return User::whereNotNull('phone_verified_at')->get()->all();
    }
}
