<?php

namespace App\Console\Commands\Watchtower;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class DiagnoseCommand extends Command
{
    protected $signature = 'watchtower:diagnose
                            {--j|json : Output as JSON}
                            {--r|report : Generate Markdown report}';

    protected $description = 'Run comprehensive diagnostics on the Watchtower system';

    protected $results = [];
    protected $passCount = 0;
    protected $warnCount = 0;
    protected $failCount = 0;

    public function handle()
    {
        $this->info('🔍 KIN Watchtower Diagnostic Tool');
        $this->line('================================');
        $this->newLine();

        $this->checkDatabase();
        $this->checkCache();
        $this->checkStorage();
        $this->checkQueue();
        $this->checkScheduler();
        $this->checkPlugins();
        $this->checkNotifications();
        $this->checkEnvironment();
        $this->checkSecurity();

        if ($this->option('json')) {
            $this->outputJson();
        } elseif ($this->option('report')) {
            $this->generateReport();
        } else {
            $this->displaySummary();
        }

        return $this->failCount > 0 ? 1 : 0;
    }

    protected function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            $this->addResult('Database', 'PASS', 'Connected successfully');
            
            $tables = DB::select('SELECT name FROM sqlite_master WHERE type="table"');
            $this->addResult('Database tables', 'PASS', count($tables) . ' tables exist');
        } catch (\Exception $e) {
            $this->addResult('Database', 'FAIL', $e->getMessage());
        }
    }

    protected function checkCache()
    {
        try {
            $key = 'watchtower_test_' . time();
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            
            if ($value === 'ok') {
                $this->addResult('Cache', 'PASS', 'Working properly');
            } else {
                $this->addResult('Cache', 'FAIL', 'Read/Write failed');
            }
        } catch (\Exception $e) {
            $this->addResult('Cache', 'FAIL', $e->getMessage());
        }
    }

    protected function checkStorage()
    {
        try {
            $freeSpace = disk_free_space('/');
            $totalSpace = disk_total_space('/');
            $freeGB = round($freeSpace / 1024 / 1024 / 1024, 2);
            $totalGB = round($totalSpace / 1024 / 1024 / 1024, 2);
            $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
            
            $message = "{$freeGB} GB free / {$totalGB} GB total ({$usedPercent}% used)";
            
            if ($usedPercent > 90) {
                $this->addResult('Storage', 'FAIL', $message . ' - CRITICAL');
            } elseif ($usedPercent > 75) {
                $this->addResult('Storage', 'WARNING', $message);
            } else {
                $this->addResult('Storage', 'PASS', $message);
            }
        } catch (\Exception $e) {
            $this->addResult('Storage', 'FAIL', $e->getMessage());
        }
    }

    protected function checkQueue()
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            
            $this->addResult('Queue', 'PASS', "Pending: {$pending}, Failed: {$failed}");
        } catch (\Exception $e) {
            $this->addResult('Queue', 'FAIL', $e->getMessage());
        }
    }

    protected function checkScheduler()
    {
        try {
            $lastRun = Cache::get('scheduler:last_run');
            if ($lastRun) {
                $this->addResult('Scheduler', 'PASS', 'Last run: ' . $lastRun);
            } else {
                $this->addResult('Scheduler', 'WARNING', 'No scheduler run recorded');
            }
        } catch (\Exception $e) {
            $this->addResult('Scheduler', 'WARNING', 'Unable to check');
        }
    }

    protected function checkPlugins()
    {
        $plugins = ['Core', 'Location', 'Security', 'Notifications', 'Device', 'Heartbeat', 'Network'];
        
        foreach ($plugins as $plugin) {
            $pluginPath = base_path("../frontend/node_modules/@kin/{$plugin}");
            if (is_dir($pluginPath)) {
                $this->addResult("Plugin: {$plugin}", 'PASS', 'Found');
            } else {
                $this->addResult("Plugin: {$plugin}", 'WARNING', 'Not found');
            }
        }
    }

    protected function checkNotifications()
    {
        $drivers = ['mail', 'log', 'twilio'];
        
        foreach ($drivers as $driver) {
            $config = config("services.{$driver}");
            if ($config && isset($config['enabled']) && $config['enabled']) {
                $this->addResult("Notification: {$driver}", 'PASS', 'Configured');
            } else {
                $this->addResult("Notification: {$driver}", 'WARNING', 'Not configured');
            }
        }
    }

    protected function checkEnvironment()
    {
        $this->addResult('Environment', 'PASS', config('app.env'));
        $this->addResult('PHP version', 'PASS', phpversion());
        $this->addResult('Laravel version', 'PASS', app()->version());
    }

    protected function checkSecurity()
    {
        if (config('app.debug')) {
            $this->addResult('Debug mode', 'WARNING', 'Enabled (disable in production)');
        } else {
            $this->addResult('Debug mode', 'PASS', 'Disabled');
        }
    }

    protected function addResult($name, $status, $message)
    {
        $this->results[] = [
            'name' => $name,
            'status' => $status,
            'message' => $message,
        ];
        
        if ($status === 'PASS') $this->passCount++;
        if ($status === 'WARNING') $this->warnCount++;
        if ($status === 'FAIL') $this->failCount++;
    }

    protected function displaySummary()
    {
        $this->newLine();
        $this->line('================================');
        $this->line('📊 DIAGNOSTIC SUMMARY');
        $this->line('================================');
        $this->newLine();
        
        foreach ($this->results as $result) {
            $icon = $result['status'] === 'PASS' ? '✅' : ($result['status'] === 'WARNING' ? '⚠️' : '❌');
            $this->line("{$icon} {$result['name']}: {$result['message']}");
        }
        
        $this->newLine();
        $this->line('================================');
        $this->line("✅ Passed: {$this->passCount}");
        $this->line("⚠️ Warnings: {$this->warnCount}");
        $this->line("❌ Failed: {$this->failCount}");
        $this->newLine();
        
        if ($this->failCount > 0) {
            $this->error('OVERALL STATUS: ❌ FAIL');
        } elseif ($this->warnCount > 0) {
            $this->warn('OVERALL STATUS: ⚠️ WARNING');
        } else {
            $this->info('OVERALL STATUS: ✅ PASS');
        }
    }

    protected function outputJson()
    {
        $this->line(json_encode([
            'status' => $this->failCount > 0 ? 'FAIL' : ($this->warnCount > 0 ? 'WARNING' : 'PASS'),
            'passed' => $this->passCount,
            'warnings' => $this->warnCount,
            'failed' => $this->failCount,
            'checks' => $this->results,
            'timestamp' => now()->toISOString(),
        ], JSON_PRETTY_PRINT));
    }

    protected function generateReport()
    {
        $report = "# KIN Watchtower Diagnostic Report\n\n";
        $report .= "**Generated:** " . now()->toDateTimeString() . "\n\n";
        $report .= "## Summary\n\n";
        $report .= "| Status | Count |\n";
        $report .= "|--------|-------|\n";
        $report .= "| ✅ Passed | {$this->passCount} |\n";
        $report .= "| ⚠️ Warnings | {$this->warnCount} |\n";
        $report .= "| ❌ Failed | {$this->failCount} |\n\n";
        $report .= "## Checks\n\n";
        $report .= "| Check | Status | Message |\n";
        $report .= "|-------|--------|---------|\n";
        
        foreach ($this->results as $result) {
            $report .= sprintf("| %s | %s | %s |\n", 
                $result['name'], 
                $result['status'], 
                $result['message']
            );
        }
        
        $filename = 'watchtower-report-' . date('Y-m-d-His') . '.md';
        File::put(storage_path('app/' . $filename), $report);
        $this->info("📄 Report generated: storage/app/{$filename}");
    }
}
