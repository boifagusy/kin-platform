<?php
namespace App\Services;
use App\Models\Version;
use App\Models\VersionChannel;

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
            return [
                'current_version_code' => $clientCode,
                'update_available' => false,
                'update_required' => false,
                'channels' => [],
            ];
        }

        $minCode = $latest->min_version_code ?? $latest->version_code;

        return [
            'current_version_code' => $clientCode,
            'current_version_name' => null,
            'latest_version_code' => $latest->version_code,
            'latest_version_name' => $latest->version_name,
            'minimum_version_code' => $minCode,
            'update_available' => $clientCode < $latest->version_code,
            'update_required' => $clientCode < $minCode,
            'force_update' => $latest->force_update,
            'policy_status' => $this->evaluatePolicy($clientCode, $platform)['policy_status'],
            'policy_reason' => $this->evaluatePolicy($clientCode, $platform)['policy_reason'],
            'policy_grace_ends' => $this->evaluatePolicy($clientCode, $platform)['policy_grace_ends'],
            'release_notes' => $latest->release_notes,
            'release_date' => $latest->release_date?->toISOString(),
            'channels' => $this->getChannels($latest->id, $platform),
        ];
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

    private function evaluatePolicy(int $clientCode, string $platform): array
    {
        return app(UpdatePolicyService::class)->evaluate($clientCode, $platform);
    }
}
