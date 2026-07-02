<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WatchtowerDiagnose extends Command
{
    protected $signature = 'watchtower:diagnose {--fix : Attempt to fix common issues}';
    protected $description = 'Run comprehensive diagnostics on the Watchtower system';

    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $warnings = 0;

    public function handle()
    {
        $this->info('🔍 KIN Watchtower Diagnostic Tool');
        $this->line('================================');
        $this->line('');

        $this->runDatabaseCheck();
        $this->runCacheCheck();
        $this->runStorageCheck();
        $this->runQueueCheck();
        $this->runSchedulerCheck();
        $this->runPluginCheck();
        $this->runNotificationCheck();
        $this->runEnvironmentCheck();
        $this->runPerformanceCheck();
        $this->runSecurityCheck();

        $this->displaySummary();

        return $this->failed > 0 ? 1 : 0;
    }

    private function runDatabaseCheck()
    {
        $this->line('📊 Database Check...');

        try {
            DB::connection()->getPdo();
            $this->recordPass('Database connection', 'Connected successfully');

            // Check tables
            $tables = ['users', 'check_ins', 'sos_events', 'jobs', 'failed_jobs', 'request_logs', 'watchtower_metrics', 'watchtower_incidents', 'watchtower_alert_rules'];
            $missing = [];
            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    $missing[] = $table;
                }
            }

            if (empty($missing)) {
                $this->recordPass('Database tables', 'All required tables exist');
            } else {
                $this->recordWarning('Database tables', 'Missing tables: ' . implode(', ', $missing));
            }

            // Check migration status
            $migrations = DB::table('migrations')->count();
            $this->recordPass('Migrations', "{$migrations} migrations applied");

        } catch (\Exception $e) {
            $this->recordFail('Database connection', $e->getMessage());
        }
    }

    private function runCacheCheck()
    {
        $this->line('💾 Cache Check...');

        try {
            $key = 'diagnose_' . time();
            Cache::put($key, 'ok', 60);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value === 'ok') {
                $this->recordPass('Cache system', 'Cache is working properly');
                $this->recordPass('Cache driver', 'Driver: ' . config('cache.default'));
            } else {
                $this->recordFail('Cache system', 'Cache write/read failed');
            }
        } catch (\Exception $e) {
            $this->recordFail('Cache system', $e->getMessage());
        }
    }

    private function runStorageCheck()
    {
        $this->line('💿 Storage Check...');

        try {
            $disk = Storage::disk('local');
            $testFile = 'diagnose_' . time() . '.txt';
            $disk->put($testFile, 'ok');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);

            if ($exists) {
                $this->recordPass('Storage system', 'Storage is working properly');
                $this->recordPass('Storage driver', 'Driver: ' . config('filesystems.default'));
            } else {
                $this->recordFail('Storage system', 'Storage write/read failed');
            }

            // Check disk space
            $total = disk_total_space('/');
            $free = disk_free_space('/');
            $used = $total - $free;
            $percentage = round(($used / $total) * 100, 2);

            if ($percentage < 80) {
                $this->recordPass('Disk space', "{$percentage}% used - Good");
            } elseif ($percentage < 90) {
                $this->recordWarning('Disk space', "{$percentage}% used - Consider cleanup");
            } else {
                $this->recordFail('Disk space', "{$percentage}% used - CRITICAL");
            }

        } catch (\Exception $e) {
            $this->recordFail('Storage system', $e->getMessage());
        }
    }

    private function runQueueCheck()
    {
        $this->line('📨 Queue Check...');

        try {
            if (Schema::hasTable('jobs')) {
                $pending = DB::table('jobs')->whereNull('reserved_at')->count();
                $processing = DB::table('jobs')->whereNotNull('reserved_at')->count();
                $this->recordPass('Queue', "Pending: {$pending}, Processing: {$processing}");
            } else {
                $this->recordWarning('Queue', 'Jobs table not found');
            }

            if (Schema::hasTable('failed_jobs')) {
                $failed = DB::table('failed_jobs')->count();
                if ($failed == 0) {
                    $this->recordPass('Failed jobs', 'No failed jobs found');
                } else {
                    $this->recordWarning('Failed jobs', "{$failed} failed jobs found");
                }
            } else {
                $this->recordWarning('Failed jobs', 'Failed jobs table not found');
            }

            $this->recordPass('Queue driver', 'Driver: ' . config('queue.default'));

        } catch (\Exception $e) {
            $this->recordFail('Queue check', $e->getMessage());
        }
    }

    private function runSchedulerCheck()
    {
        $this->line('⏰ Scheduler Check...');

        try {
            $lastRun = Cache::get('scheduler_last_run');
            if ($lastRun) {
                $minutes = now()->diffInMinutes($lastRun);
                if ($minutes < 10) {
                    $this->recordPass('Scheduler', "Last run: {$minutes} minutes ago");
                } else {
                    $this->recordWarning('Scheduler', "Last run: {$minutes} minutes ago - may be stalled");
                }
            } else {
                $this->recordWarning('Scheduler', 'No scheduler run recorded');
            }
        } catch (\Exception $e) {
            $this->recordFail('Scheduler check', $e->getMessage());
        }
    }

    private function runPluginCheck()
    {
        $this->line('🔌 Plugin Check...');

        $plugins = [
            'kin-core' => 'Core',
            'kin-location' => 'Location',
            'kin-security' => 'Security',
            'kin-notifications' => 'Notifications',
            'kin-device' => 'Device',
            'kin-heartbeat' => 'Heartbeat',
            'kin-network' => 'Network',
            'kin-storage' => 'Storage'
        ];

        $pluginDir = base_path('../plugins');
        foreach ($plugins as $dir => $name) {
            if (is_dir($pluginDir . '/' . $dir)) {
                $this->recordPass("Plugin: {$name}", 'Found');
            } else {
                $this->recordWarning("Plugin: {$name}", 'Not found');
            }
        }
    }

    private function runNotificationCheck()
    {
        $this->line('📱 Notification Check...');

        $providers = ['mail', 'log', 'twilio'];
        foreach ($providers as $provider) {
            $config = config("services.{$provider}");
            if ($config) {
                $this->recordPass("Notification: {$provider}", 'Configured');
            } else {
                $this->recordWarning("Notification: {$provider}", 'Not configured');
            }
        }
    }

    private function runEnvironmentCheck()
    {
        $this->line('🌍 Environment Check...');

        $this->recordPass('Environment', app()->environment());
        $this->recordPass('App version', config('app.version', '1.0.0'));
        $this->recordPass('PHP version', phpversion());
        $this->recordPass('Laravel version', app()->version());

        if (config('app.debug')) {
            $this->recordWarning('Debug mode', 'Debug mode is enabled (production should have it disabled)');
        } else {
            $this->recordPass('Debug mode', 'Disabled');
        }
    }

    private function runPerformanceCheck()
    {
        $this->line('⚡ Performance Check...');

        $memory = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $usagePercent = round(($memory / $this->convertToBytes($memoryLimit)) * 100, 2);

        if ($usagePercent < 80) {
            $this->recordPass('Memory usage', "{$usagePercent}%");
        } else {
            $this->recordWarning('Memory usage', "{$usagePercent}%");
        }

        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $this->recordPass('CPU load', "1min: {$load[0]}, 5min: {$load[1]}, 15min: {$load[2]}");
        }
    }

    private function runSecurityCheck()
    {
        $this->line('🔐 Security Check...');

        // Check .env file permissions
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $perms = substr(sprintf('%o', fileperms($envPath)), -4);
            if ($perms === '0600' || $perms === '0644') {
                $this->recordPass('.env permissions', $perms);
            } else {
                $this->recordWarning('.env permissions', "{$perms} - Consider 0600");
            }
        }

        // Check storage permissions
        $storagePath = storage_path();
        if (is_writable($storagePath)) {
            $this->recordPass('Storage permissions', 'Writable');
        } else {
            $this->recordFail('Storage permissions', 'Not writable');
        }
    }

    private function convertToBytes(string $limit): int
    {
        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        return $value;
    }

    private function recordPass(string $check, string $message)
    {
        $this->results[] = ['status' => '✅ PASS', 'check' => $check, 'message' => $message];
        $this->passed++;
    }

    private function recordWarning(string $check, string $message)
    {
        $this->results[] = ['status' => '⚠️ WARNING', 'check' => $check, 'message' => $message];
        $this->warnings++;
    }

    private function recordFail(string $check, string $message)
    {
        $this->results[] = ['status' => '❌ FAIL', 'check' => $check, 'message' => $message];
        $this->failed++;
    }

    private function displaySummary()
    {
        $this->line('');
        $this->line('================================');
        $this->line('📊 DIAGNOSTIC SUMMARY');
        $this->line('================================');
        $this->line('');

        foreach ($this->results as $result) {
            $this->line("{$result['status']} {$result['check']}: {$result['message']}");
        }

        $this->line('');
        $this->line('================================');
        $this->line("✅ Passed: {$this->passed}");
        $this->line("⚠️ Warnings: {$this->warnings}");
        $this->line("❌ Failed: {$this->failed}");

        $overall = $this->failed === 0 ? '✅ PASS' : '❌ FAIL';
        $this->line('');
        $this->line("OVERALL STATUS: {$overall}");

        if ($this->failed > 0) {
            $this->line('');
            $this->line('🚨 Recommendations:');
            foreach ($this->results as $result) {
                if ($result['status'] === '❌ FAIL') {
                    $this->line("  - Fix {$result['check']}: {$result['message']}");
                }
            }
        }
    }
}
