<?php

namespace App\Services;

use App\Models\UpdatePolicy;
use App\Models\Version;
use Illuminate\Support\Facades\Log;

class UpdatePolicyService
{
    const POLICIES = ['optional', 'recommended', 'required'];
    const PLATFORMS = ['android', 'ios', 'web', 'all'];
    const REASONS = ['minimum_version', 'security_update', 'recommended_release', 'critical_bugfix'];

    const PRIORITY_MAP = [
        'required' => 3,
        'recommended' => 2,
        'optional' => 1,
    ];

    public function evaluate(int $clientCode, string $platform): array
    {
        $version = app(VersionService::class)->getActive();

        if (!$version) {
            Log::info('[B3] Policy evaluation — no active version');
            return $this->result('up_to_date');
        }

        $policies = UpdatePolicy::active()
            ->forPlatform($platform)
            ->inWindow()
            ->where('version_id', $version->id)
            ->orderBy('priority', 'desc')
            ->get();

        if ($policies->isEmpty()) {
            Log::info('[B3] Policy evaluation — no matching policies', [
                'platform' => $platform,
                'version_id' => $version->id,
            ]);
            return $this->result('up_to_date');
        }

        $matched = null;
        $highestPriority = -1;

        foreach ($policies as $policy) {
            $effectivePriority = ($policy->priority * 10) + (self::PRIORITY_MAP[$policy->policy] ?? 0);
            if ($effectivePriority > $highestPriority) {
                $highestPriority = $effectivePriority;
                $matched = $policy;
            }
        }

        $status = $matched->policy;
        $graceEnds = null;

        if ($matched->grace_days > 0 && $version->release_date) {
            $graceEnd = $version->release_date->addDays($matched->grace_days);
            if (now()->lt($graceEnd)) {
                $status = $status === 'required' ? 'recommended' : 'optional';
                $graceEnds = $graceEnd->toISOString();
            }
        }

        Log::info('[B3] Policy matched', [
            'client_code' => $clientCode,
            'platform' => $platform,
            'policy_id' => $matched->id,
            'status' => $status,
            'grace_ends' => $graceEnds,
        ]);

        return [
            'policy_status' => $status,
            'policy_reason' => $matched->reason,
            'policy_grace_ends' => $graceEnds,
            'matched_policy_id' => $matched->id,
        ];
    }

    public function create(array $data): UpdatePolicy
    {
        return UpdatePolicy::create($data);
    }

    public function update(UpdatePolicy $policy, array $data): UpdatePolicy
    {
        $policy->update($data);
        return $policy;
    }

    public function delete(UpdatePolicy $policy): void
    {
        $policy->delete();
    }

    private function result(string $status): array
    {
        return [
            'policy_status' => $status,
            'policy_reason' => null,
            'policy_grace_ends' => null,
            'matched_policy_id' => null,
        ];
    }
}
