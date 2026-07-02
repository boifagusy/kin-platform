<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class RotateLogsAction extends BaseAction
{
    protected string $name = 'rotate_logs';
    protected string $description = 'Rotate application logs';
    
    public function execute(): RecoveryResult
    {
        try {
            $logPath = storage_path('logs');
            $rotated = 0;
            
            if (File::exists($logPath)) {
                $files = File::files($logPath);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'log' && $file->getSize() > 10 * 1024 * 1024) {
                        $newName = $file->getPath() . '/' . $file->getFilenameWithoutExtension() . '_' . date('Y-m-d_H-i-s') . '.log';
                        File::move($file->getPathname(), $newName);
                        $rotated++;
                    }
                }
            }
            
            Log::info('Logs rotated', ['rotated' => $rotated]);
            
            return $this->success('Logs rotated successfully', [
                'rotated' => $rotated,
                'log_path' => $logPath
            ]);
        } catch (\Exception $e) {
            Log::error('Rotate logs failed: ' . $e->getMessage());
            return $this->failed('Failed to rotate logs: ' . $e->getMessage());
        }
    }
}
