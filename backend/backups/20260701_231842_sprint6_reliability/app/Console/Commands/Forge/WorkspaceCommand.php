<?php

namespace App\Console\Commands\Forge;

use App\Services\Forge\ForgeService;
use Illuminate\Console\Command;

class WorkspaceCommand extends Command
{
    protected $signature = 'kin:workspace';
    protected $description = 'Create an ephemeral build workspace';

    public function handle(ForgeService $forge)
    {
        $this->info('🔨 KIN Workspace');
        $this->line('');

        $this->info('Creating workspace...');
        $result = $forge->workspace();

        $this->line('');
        $this->line('Steps:');
        foreach ($result['steps'] as $step => $status) {
            $this->line(sprintf('  ├── %s: %s', ucfirst($step), $status));
        }

        if (isset($result['apk'])) {
            $this->line('');
            $this->info('📱 APK built successfully!');
            $this->line(sprintf('  Location: %s', $result['apk']));
            $this->line(sprintf('  Size: %s', $result['apk_size']));
        }

        $this->line('');
        $this->info("Workspace: {$result['workspace']}");
    }
}
