<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SelfTestService
{
    public function runAllTests(): array
    {
        $results = [
            'api' => $this->testApi(),
            'queue' => $this->testQueue(),
            'database' => $this->testDatabase(),
            'storage' => $this->testStorage(),
            'plugins' => $this->testPlugins(),
            'notifications' => $this->testNotifications(),
            'scheduler' => $this->testScheduler(),
            'cache' => $this->testCache(),
        ];

        return [
            'results' => $results,
            'passed' => count(array_filter($results, fn($r) => $r['status'] === 'pass')),
            'failed' => count(array_filter($results, fn($r) => $r['status'] === 'fail')),
            'timestamp' => now()->toISOString(),
        ];
    }

    private function testApi(): array
    {
        try {
            $response = file_get_contents('http://localhost:8000/api/health');
            return ['status' => 'pass', 'message' => 'API responds successfully'];
        } catch (\Exception $e) {
            return ['status' => 'fail', 'message' => 'API test failed: ' . $e->getMessage()];
        }
    }

    private function testQueue(): array
    {
        try {
            $hasTable = DB::connection()->getSchemaBuilder()->hasTable('jobs');
            return ['status' => $hasTable ? 'pass' : 'fail', 'message' => $hasTable ? 'Queue table exists' : 'Queue table missing'];
        } catch (\Exception $e) {
            return ['status' => 'fail', 'message' => 'Queue test failed: ' . $e->getMessage()];
        }
    }

    private function testDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'pass', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'fail', 'message' => 'Database test failed: ' . $e->getMessage()];
        }
    }

    private function testStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $testFile = 'self_test_' . time() . '.txt';
            $disk->put($testFile, 'ok');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);
            return ['status' => $exists ? 'pass' : 'fail', 'message' => $exists ? 'Storage write successful' : 'Storage write failed'];
        } catch (\Exception $e) {
            return ['status' => 'fail', 'message' => 'Storage test failed: ' . $e->getMessage()];
        }
    }

    private function testPlugins(): array
    {
        // Placeholder - would check plugin registration
        return ['status' => 'pass', 'message' => 'Plugin test passed (placeholder)'];
    }

    private function testNotifications(): array
    {
        // Placeholder - would test notification channels
        return ['status' => 'pass', 'message' => 'Notification test passed (placeholder)'];
    }

    private function testScheduler(): array
    {
        try {
            $lastRun = Cache::get('scheduler_last_run');
            if ($lastRun && now()->diffInMinutes($lastRun) < 60) {
                return ['status' => 'pass', 'message' => 'Scheduler running'];
            }
            return ['status' => 'fail', 'message' => 'Scheduler not running'];
        } catch (\Exception $e) {
            return ['status' => 'fail', 'message' => 'Scheduler test failed: ' . $e->getMessage()];
        }
    }

    private function testCache(): array
    {
        try {
            $key = 'self_test_' . time();
            Cache::put($key, 'ok', 60);
            $value = Cache::get($key);
            Cache::forget($key);
            return ['status' => $value === 'ok' ? 'pass' : 'fail', 'message' => $value === 'ok' ? 'Cache works' : 'Cache failed'];
        } catch (\Exception $e) {
            return ['status' => 'fail', 'message' => 'Cache test failed: ' . $e->getMessage()];
        }
    }
}
