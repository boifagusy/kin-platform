<?php

namespace App\Services;

use App\Models\Version;
use App\Models\VersionChannel;
use Illuminate\Support\Facades\Log;

class VersionService
{
    const ALLOWED_CHANNELS = [
        'google_play', 'apk', 'huawei_appgallery',
        'samsung_store', 'amazon_appstore',
        'apple_app_store', 'enterprise',
    ];

    public function create(array $data): Version
    {
        return Version::create($data);
    }

    public function update(Version $version, array $data): Version
    {
        $version->update($data);
        return $version;
    }

    public function delete(Version $version): void
    {
        $version->delete();
    }

    public function activate(int $id): Version
    {
        Version::where('is_active', true)->update(['is_active' => false]);
        $version = Version::findOrFail($id);
        $version->update(['is_active' => true]);
        return $version;
    }

    public function getActive(): ?Version
    {
        return Version::active()->first();
    }

    public function compareVersion(int $clientCode, string $platform): array
    {
        $latest = $this->getActive();

        if (!$latest) {
            Log::info('[B3] Version check — no active version', [
                'client_code' => $clientCode,
                'platform' => $platform,
            ]);

            return [
                'current_version_code' => $clientCode,
                'update_available' => false,
                'update_required' => false,
                'channels' => [],
            ];
        }

        $minCode = $latest->min_version_code ?? $latest->version_code;
        $policy = app(UpdatePolicyService::class)->evaluate($clientCode, $platform);
        $updateAvailable = $clientCode < $latest->version_code;
        $updateRequired = $clientCode < $minCode;

        $severity = $this->calculateSeverity($updateAvailable, $updateRequired, $policy['policy_status']);

        Log::info('[B3] Version comparison', [
            'client_code' => $clientCode,
            'platform' => $platform,
            'latest_code' => $latest->version_code,
            'min_code' => $minCode,
            'update_available' => $updateAvailable,
            'severity' => $severity,
        ]);

        return [
            'current_version_code' => $clientCode,
            'current_version_name' => null,
            'latest_version_code' => $latest->version_code,
            'latest_version_name' => $latest->version_name,
            'minimum_version_code' => $minCode,
            'update_available' => $updateAvailable,
            'update_required' => $updateRequired,
            'force_update' => $latest->force_update,
            'update_severity' => $severity,
            'policy_status' => $policy['policy_status'],
            'policy_reason' => $policy['policy_reason'],
            'policy_grace_ends' => $policy['policy_grace_ends'],
            'release_notes' => $latest->release_notes,
            'release_date' => $latest->release_date?->toISOString(),
            'channels' => $this->getChannels($latest->id, $platform),
        ];
    }

    private function calculateSeverity(bool $updateAvailable, bool $updateRequired, string $policyStatus): string
    {
        if (!$updateAvailable) {
            return 'current';
        }

        if ($policyStatus === 'required') {
            return 'force';
        }

        if ($updateRequired) {
            return 'required';
        }

        return 'optional';
    }

    public function getChannels(int $versionId, string $platform): array
    {
        return VersionChannel::forPlatform($platform)
            ->where('version_id', $versionId)
            ->get()
            ->map(fn($c) => [
                'channel' => $c->channel,
                'url' => $c->download_url,
            ])
            ->toArray();
    }

    public function addChannel(Version $version, array $data): VersionChannel
    {
        return $version->channels()->create($data);
    }

    public function removeChannel(VersionChannel $channel): void
    {
        $channel->delete();
    }
}
