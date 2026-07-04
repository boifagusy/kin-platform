<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ClearCacheAction extends BaseAction
{
    protected string $name = 'clear_cache';
    protected string $description = 'Clear application cache';
    
    public function execute(): RecoveryResult
    {
        try {
            Artisan::call('cache:clear');
            $output = Artisan::output();
            
            Artisan::call('config:clear');
            
            Log::info('Cache cleared');
            
            return $this->success('Cache cleared successfully', [
                'output' => $output,
                'time' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Clear cache failed: ' . $e->getMessage());
            return $this->failed('Failed to clear cache: ' . $e->getMessage());
        }
    }
}
