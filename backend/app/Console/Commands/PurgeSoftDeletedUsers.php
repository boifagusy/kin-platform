<?php

namespace App\Console\Commands;

use App\Services\UserDeletionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeSoftDeletedUsers extends Command
{
    protected $signature = 'users:purge';
    protected $description = 'Permanently delete soft-deleted users based on retention setting';

    private UserDeletionService $deletionService;

    public function __construct(UserDeletionService $deletionService)
    {
        parent::__construct();
        $this->deletionService = $deletionService;
    }

    public function handle()
    {
        try {
            $result = $this->deletionService->purgeExpiredUsers();

            if ($result['skipped'] ?? false) {
                $this->info("Purge skipped: {$result['reason']}");
                Log::info('[users:purge] Command executed - purge skipped', $result);
                return 0;
            }

            $deleted = $result['deleted'] ?? 0;
            $this->info("Successfully purged {$deleted} expired user(s)");
            Log::info('[users:purge] Command executed', ['deleted' => $deleted]);
            return 0;

        } catch (\Exception $e) {
            $this->error("Purge failed: {$e->getMessage()}");
            Log::error('[users:purge] Command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
