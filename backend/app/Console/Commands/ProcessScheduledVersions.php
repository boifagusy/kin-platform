<?php

namespace App\Console\Commands;

use App\Models\UpdatePolicy;
use App\Models\Version;
use App\Services\VersionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledVersions extends Command
{
    protected $signature = 'version:process-scheduled';
    protected $description = 'Activate or expire versions based on policy scheduling';

    public function handle(VersionService $service): int
    {
        // Activate: starts_at <= now AND version not yet active
        $toActivate = UpdatePolicy::where('starts_at', '<=', now())
            ->where('is_active', true)
            ->whereHas('version', fn($q) => $q->where('is_active', false))
            ->orderBy('priority', 'desc')
            ->orderBy('version_id', 'desc')
            ->get();

        foreach ($toActivate as $policy) {
            $service->activate($policy->version_id);
            Log::info('[B5] Scheduler Activated', [
                'version_id' => $policy->version_id,
                'policy_id' => $policy->id,
            ]);
            $this->info("Activated version {$policy->version_id}");
        }

        // Expire: expires_at <= now AND version is active
        $toExpire = UpdatePolicy::where('expires_at', '<=', now())
            ->where('is_active', true)
            ->whereHas('version', fn($q) => $q->where('is_active', true))
            ->get();

        foreach ($toExpire as $policy) {
            $version = Version::find($policy->version_id);
            if ($version && $version->is_active) {
                $version->update(['is_active' => false]);
                Log::info('[B5] Scheduler Expired', [
                    'version_id' => $policy->version_id,
                    'policy_id' => $policy->id,
                ]);
                $this->info("Expired version {$policy->version_id}");
            }
        }

        return self::SUCCESS;
    }
}
