<?php

namespace App\Console\Commands\Forge;

use App\Services\Forge\ForgeService;
use Illuminate\Console\Command;

class BuildCommand extends Command
{
    protected $signature = 'kin:build';
    protected $description = 'Full build with all guards';

    public function handle(ForgeService $forge)
    {
        $this->info('🏗️ KIN Build');
        $this->line('');

        // Step 1: Run doctor
        $this->info('Step 1: Running doctor...');
        $doctor = $forge->doctor();
        
        $failed = 0;
        foreach ($doctor as $check) {
            if ($check['status'] === 'fail') {
                $failed++;
                $this->line(sprintf('  ❌ %s: %s', $check['name'], $check['message']));
            }
        }

        if ($failed > 0) {
            $this->error("Build aborted: {$failed} doctor check(s) failed.");
            $this->line('Run: php artisan kin:doctor to see details');
            return 1;
        }

        $this->info('✅ Doctor passed');

        // Step 2: Cleanup
        $this->info('Step 2: Running cleanup...');
        $forge->cleanup();
        $this->info('✅ Cleanup done');

        // Step 3: Build
        $this->info('Step 3: Building...');
        $result = $forge->workspace();

        if (isset($result['apk'])) {
            $this->line('');
            $this->info('✅ Build successful!');
            $this->line(sprintf('📱 APK: %s (%s)', $result['apk'], $result['apk_size']));
            return 0;
        }

        $this->error('Build failed. Check workspace logs.');
        return 1;
    }
}
