<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserDeletionService
{
    /**
     * Purge soft-deleted users based on retention setting
     */
    public function purgeExpiredUsers(): array
    {
        $retentionDays = $this->getRetentionDays();

        // If retention is 0 (never delete), skip purge
        if ($retentionDays === 0) {
            Log::info('[UserDeletion] Purge skipped: retention set to "Never Delete"');
            return ['skipped' => true, 'reason' => 'retention_disabled', 'deleted' => 0];
        }

        try {
            // Find soft-deleted users older than retention period
            $cutoffDate = now()->subDays($retentionDays);
            
            $expiredUsers = User::onlyTrashed()
                ->where('deleted_at', '<', $cutoffDate)
                ->get();

            $count = $expiredUsers->count();

            if ($count === 0) {
                Log::info('[UserDeletion] No expired users to delete', [
                    'retention_days' => $retentionDays,
                    'cutoff_date' => $cutoffDate
                ]);
                return ['skipped' => false, 'deleted' => 0];
            }

            // Permanently delete
            $expiredUsers->each(function ($user) {
                $user->forceDelete();
            });

            Log::info('[UserDeletion] Purge completed', [
                'retention_days' => $retentionDays,
                'deleted_count' => $count,
                'cutoff_date' => $cutoffDate
            ]);

            return ['skipped' => false, 'deleted' => $count];

        } catch (\Exception $e) {
            Log::error('[UserDeletion] Purge failed', [
                'error' => $e->getMessage(),
                'retention_days' => $retentionDays
            ]);
            throw $e;
        }
    }

    /**
     * Get retention days from settings
     */
    private function getRetentionDays(): int
    {
        $setting = \App\Models\SystemSetting::where('key', 'deleted_account_retention_days')->first();
        return $setting ? (int)$setting->value : 30; // Default to 30 if not set
    }
}
