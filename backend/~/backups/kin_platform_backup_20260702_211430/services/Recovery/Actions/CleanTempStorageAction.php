<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanTempStorageAction extends BaseAction
{
    protected string $name = 'clean_temp_storage';
    protected string $description = 'Clean temporary storage files';
    
    public function execute(): RecoveryResult
    {
        try {
            $tempPath = storage_path('framework/cache');
            $deleted = 0;
            $size = 0;
            
            if (File::exists($tempPath)) {
                $files = File::allFiles($tempPath);
                $threshold = Carbon::now()->subDay();
                
                foreach ($files as $file) {
                    if (File::lastModified($file) < $threshold->timestamp) {
                        $size += $file->getSize();
                        File::delete($file);
                        $deleted++;
                    }
                }
            }
            
            Log::info('Temporary storage cleaned', ['deleted' => $deleted, 'size' => $size]);
            
            return $this->success('Temporary storage cleaned', [
                'deleted_count' => $deleted,
                'deleted_size_bytes' => $size,
                'deleted_size_mb' => round($size / 1024 / 1024, 2),
                'path' => $tempPath
            ]);
        } catch (\Exception $e) {
            Log::error('Clean temp storage failed: ' . $e->getMessage());
            return $this->failed('Failed to clean temp storage: ' . $e->getMessage());
        }
    }
}
