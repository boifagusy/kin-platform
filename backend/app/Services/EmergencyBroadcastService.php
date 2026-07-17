<?php
namespace App\Services;
use App\Models\EmergencyBroadcast;
use App\Jobs\SendEmergencyBroadcastJob;

class EmergencyBroadcastService
{
    public function create(array $data): EmergencyBroadcast
    {
        return EmergencyBroadcast::create($data);
    }

    public function activate(EmergencyBroadcast $broadcast): EmergencyBroadcast
    {
        $broadcast->update(['status' => 'active']);
        SendEmergencyBroadcastJob::dispatch($broadcast);
        return $broadcast;
    }

    public function cancel(EmergencyBroadcast $broadcast): EmergencyBroadcast
    {
        $broadcast->update(['status' => 'cancelled']);
        return $broadcast;
    }

    public function getActive(): array
    {
        return EmergencyBroadcast::active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
