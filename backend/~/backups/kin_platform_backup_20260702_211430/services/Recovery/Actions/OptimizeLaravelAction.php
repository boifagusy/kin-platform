<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class OptimizeLaravelAction extends BaseAction
{
    protected string $name = 'optimize_laravel';
    protected string $description = 'Optimize Laravel application';
    
    public function execute(): RecoveryResult
    {
        try {
            $results = [];
            
            Artisan::call('cache:clear');
            $results['cache_clear'] = 'success';
            
            Artisan::call('config:clear');
            $results['config_clear'] = 'success';
            
            Artisan::call('route:clear');
            $results['route_clear'] = 'success';
            
            Artisan::call('view:clear');
            $results['view_clear'] = 'success';
            
            Artisan::call('optimize');
            $results['optimize'] = 'success';
            
            Log::info('Laravel optimized', $results);
            
            return $this->success('Laravel optimized successfully', $results);
        } catch (\Exception $e) {
            Log::error('Optimize Laravel failed: ' . $e->getMessage());
            return $this->failed('Failed to optimize Laravel: ' . $e->getMessage());
        }
    }
}
