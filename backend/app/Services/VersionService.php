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
        $version = Version::create($data);
        Log::info('[B5] Create Version', ['id' => $version->id, 'code' => $version->version_code]);
        return $version;
    }

    public function update(Version $version, array $data): Version
    {
        $version->update($data);
        Log::info('[B5] Update Version', ['id' => $version->id, 'code' => $version->version_code]);
        return $version;
    }

    public function delete(Version $version): void
    {
        $version->delete();
        Log::info('[B5] Delete Version', ['id' => $version->id, 'code' => $version->version_code]);
    }

    public function restore(int $id): Version
    {
        $version = Version::withTrashed()->findOrFail($id);
        $version->restore();
        Log::info('[B5] Restore Version', ['id' => $version->id, 'code' => $version->version_code]);
        return $version;
    }

    public function activate(int $id): Version
    {
        Version::where('is_active', true)->update(['is_active' => false]);
        $version = Version::findOrFail($id);
        $version->update(['is_active' => true]);
        Log::info('[B5] Activate Version', ['id' => $version->id, 'code' => $version->version_code]);
        return $version;
    }

    public function getActive(): ?Version
    {
        return Version::active()->first();
    }

    public function getAllWithTrashed()
    {
        return Version::withTrashed()->orderBy('version_code', 'desc')->get();
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
        if (!$updateAvailable) return 'current';
        if ($policyStatus === 'required') return 'force';
        if ($updateRequired) return 'required';
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
        $channel = $version->channels()->create($data);
        Log::info('[B5] Add Channel', ['version_id' => $version->id, 'channel' => $channel->channel]);
        return $channel;
    }

    public function removeChannel(VersionChannel $channel): void
    {
        Log::info('[B5] Remove Channel', ['id' => $channel->id, 'channel' => $channel->channel]);
        $channel->delete();
    }

    public function submitForReview(Version $version): Version
    {
        if ($version->status !== Version::STATUS_DRAFT && $version->status !== Version::STATUS_REJECTED) {
            throw new \InvalidArgumentException('Only draft or rejected versions can be submitted.');
        }
        $version->update(['status' => Version::STATUS_REVIEW]);
        $this->logAction('version_submitted', $version);
        return $version;
    }

    public function approve(Version $version, int $adminId): Version
    {
        if ($version->status !== Version::STATUS_REVIEW) {
            throw new \InvalidArgumentException('Only versions under review can be approved.');
        }
        $version->update([
            'status' => Version::STATUS_APPROVED,
            'approved_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        $this->logAction('version_approved', $version);
        return $version;
    }

    public function reject(Version $version, int $adminId): Version
    {
        if ($version->status !== Version::STATUS_REVIEW) {
            throw new \InvalidArgumentException('Only versions under review can be rejected.');
        }
        $version->update([
            'status' => Version::STATUS_REJECTED,
            'reviewed_at' => now(),
        ]);
        $this->logAction('version_rejected', $version);
        return $version;
    }

    public function archive(Version $version): Version
    {
        $version->update([
            'status' => Version::STATUS_ARCHIVED,
            'is_active' => false,
        ]);
        $this->logAction('version_archived', $version);
        return $version;
    }

    private function logAction(string $action, Version $version): void
    {
        \App\Models\AdminLog::create([
            'admin_user_id' => auth('admin')->id() ?? 1,
            'action' => $action,
            'entity_type' => 'version',
            'entity_id' => $version->id,
            'new_values' => json_encode(['status' => $version->status, 'version_code' => $version->version_code]),
            'ip_address' => request()->ip(),
        ]);
    }
}
