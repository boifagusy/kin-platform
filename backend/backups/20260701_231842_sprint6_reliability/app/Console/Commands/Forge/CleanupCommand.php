<?php

namespace App\Console\Commands\Forge;

use App\Services\Forge\ForgeService;
use Illuminate\Console\Command;

class CleanupCommand extends Command
{
    protected $signature = 'kin:cleanup {--dry-run : Show what would be cleaned without actually cleaning}';
    protected $description = 'Clean KIN caches and temporary files';

    public function handle(ForgeService $forge)
    {
        if ($this->option('dry-run')) {
            $this->info('🧹 KIN Cleanup Report (DRY RUN)');
            $this->line('');
            $this->line('Would clean:');
            $this->line('  - Gradle cache');
            $this->line('  - npm cache');
            $this->line('  - Laravel cache');
            $this->line('  - Old logs (>7 days)');
            $this->line('  - Android build files');
            return 0;
        }

        $this->info('🧹 KIN Cleanup');
        $this->line('');

        $results = $forge->cleanup();

        $this->line('Cleaned:');
        foreach ($results as $key => $value) {
            $this->line(sprintf('  ├── %s: %s', ucfirst(str_replace('_', ' ', $key)), $value));
        }

        $this->line('');
        $this->info('✅ Cleanup complete!');
    }
}
