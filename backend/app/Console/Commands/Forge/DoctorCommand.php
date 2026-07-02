<?php

namespace App\Console\Commands\Forge;

use App\Services\Forge\ForgeService;
use Illuminate\Console\Command;

class DoctorCommand extends Command
{
    protected $signature = 'kin:doctor';
    protected $description = 'Run KIN environment diagnostics';

    public function handle(ForgeService $forge)
    {
        $this->info('🔍 KIN Doctor Report');
        $this->line('');

        $results = $forge->doctor();
        $failed = 0;

        foreach ($results as $check) {
            $status = $check['status'] === 'pass' ? '✅' : '❌';
            $this->line(sprintf('  %s %s: %s', $status, $check['name'], $check['message']));
            
            if ($check['status'] === 'fail') {
                $failed++;
                if (isset($check['required'])) {
                    $this->line(sprintf('     Required: %s', $check['required']));
                }
            }
        }

        $this->line('');
        $total = count($results);
        $passed = $total - $failed;

        $status = $failed === 0 ? '✅ ALL CHECKS PASSED' : "⚠️ {$failed} issue(s) found";
        $this->info("Status: {$status} ({$passed}/{$total} checks)");

        return $failed === 0 ? 0 : 1;
    }
}
