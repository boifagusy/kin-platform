<?php

namespace App\Services\Forge;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class ForgeService
{
    /**
     * Run doctor check
     */
    public function doctor(): array
    {
        return [
            'storage' => $this->checkStorage(),
            'php' => $this->checkPhp(),
            'composer' => $this->checkComposer(),
            'node' => $this->checkNode(),
            'npm' => $this->checkNpm(),
            'java' => $this->checkJava(),
            'android_sdk' => $this->checkAndroidSdk(),
            'gradle' => $this->checkGradle(),
            'git' => $this->checkGit(),
            'capacitor' => $this->checkCapacitor(),
            'database' => $this->checkDatabase(),
            'environment' => $this->checkEnvironment(),
        ];
    }

    private function checkStorage(): array
    {
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        $freeGB = round($freeSpace / 1024 / 1024 / 1024, 2);
        $totalGB = round($totalSpace / 1024 / 1024 / 1024, 2);
        $percent = round(($freeSpace / $totalSpace) * 100, 2);

        return [
            'name' => 'Storage',
            'status' => $freeGB > 0.5 ? 'pass' : 'fail',
            'message' => "{$freeGB} GB free / {$totalGB} GB total ({$percent}%)",
            'threshold' => '0.5 GB',
        ];
    }

    private function checkPhp(): array
    {
        $version = phpversion();
        $status = version_compare($version, '8.3.0', '>=') ? 'pass' : 'fail';
        return [
            'name' => 'PHP',
            'status' => $status,
            'message' => "v{$version}",
            'required' => '>= 8.3.0',
        ];
    }

    private function checkComposer(): array
    {
        $result = Process::run('composer --version 2>/dev/null');
        $status = $result->successful() ? 'pass' : 'fail';
        $version = $result->successful() ? trim($result->output()) : 'Not found';
        return [
            'name' => 'Composer',
            'status' => $status,
            'message' => $version,
            'required' => 'Installed',
        ];
    }

    private function checkNode(): array
    {
        $result = Process::run('node --version 2>/dev/null');
        $status = $result->successful() ? 'pass' : 'fail';
        $version = $result->successful() ? trim($result->output()) : 'Not found';
        return [
            'name' => 'Node.js',
            'status' => $status,
            'message' => $version,
            'required' => '>= 18.0.0',
        ];
    }

    private function checkNpm(): array
    {
        $result = Process::run('npm --version 2>/dev/null');
        $status = $result->successful() ? 'pass' : 'fail';
        $version = $result->successful() ? trim($result->output()) : 'Not found';
        return [
            'name' => 'npm',
            'status' => $status,
            'message' => "v{$version}",
            'required' => 'Installed',
        ];
    }

    private function checkJava(): array
    {
        $result = Process::run('java -version 2>&1');
        $status = $result->successful() ? 'pass' : 'fail';
        $version = $result->successful() ? trim(explode("\n", $result->output())[0]) : 'Not found';
        return [
            'name' => 'Java',
            'status' => $status,
            'message' => $version,
            'required' => '>= 17.0.0',
        ];
    }

    private function checkAndroidSdk(): array
    {
        $sdkPath = env('ANDROID_HOME', '/data/data/com.termux/files/usr/lib/android-sdk');
        $exists = File::exists($sdkPath);
        return [
            'name' => 'Android SDK',
            'status' => $exists ? 'pass' : 'fail',
            'message' => $exists ? $sdkPath : 'Not found',
            'required' => 'ANDROID_HOME set',
        ];
    }

    private function checkGradle(): array
    {
        $result = Process::run('gradle --version 2>/dev/null');
        $status = $result->successful() ? 'pass' : 'fail';
        $version = $result->successful() ? trim(explode("\n", $result->output())[0]) : 'Not found';
        return [
            'name' => 'Gradle',
            'status' => $status,
            'message' => $version,
            'required' => 'Installed',
        ];
    }

    private function checkGit(): array
    {
        $result = Process::run('git --version 2>/dev/null');
        $status = $result->successful() ? 'pass' : 'fail';
        $version = $result->successful() ? trim($result->output()) : 'Not found';
        return [
            'name' => 'Git',
            'status' => $status,
            'message' => $version,
            'required' => 'Installed',
        ];
    }

    private function checkCapacitor(): array
    {
        $projectRoot = dirname(__DIR__, 3);
        $configPath = $projectRoot . '/frontend/capacitor.config.json';
        $exists = File::exists($configPath);
        return [
            'name' => 'Capacitor',
            'status' => $exists ? 'pass' : 'fail',
            'message' => $exists ? 'Configured' : 'Not found',
            'required' => 'capacitor.config.json exists',
        ];
    }

    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();
            return [
                'name' => 'Database',
                'status' => 'pass',
                'message' => 'Connected',
                'required' => 'Working',
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Database',
                'status' => 'fail',
                'message' => 'Connection failed',
                'required' => 'Working',
            ];
        }
    }

    private function checkEnvironment(): array
    {
        $envFile = base_path('.env');
        $exists = File::exists($envFile);
        return [
            'name' => 'Environment',
            'status' => $exists ? 'pass' : 'fail',
            'message' => $exists ? 'Configured' : 'Missing .env',
            'required' => '.env exists',
        ];
    }

    /**
     * Run cleanup
     */
    public function cleanup(): array
    {
        $cleaned = [];

        // Clean Gradle cache
        $gradleCache = getenv('HOME') . '/.gradle/caches';
        if (File::exists($gradleCache)) {
            $size = $this->getDirectorySize($gradleCache);
            File::deleteDirectory($gradleCache);
            $cleaned['gradle'] = $size;
        }

        // Clean npm cache
        $npmCache = getenv('HOME') . '/.npm/_cacache';
        if (File::exists($npmCache)) {
            $size = $this->getDirectorySize($npmCache);
            File::deleteDirectory($npmCache);
            $cleaned['npm'] = $size;
        }

        // Clean Laravel cache
        $this->clearLaravelCache();
        $cleaned['laravel'] = 'Cache cleared';

        // Clean logs older than 7 days
        $logDir = storage_path('logs');
        if (File::exists($logDir)) {
            $count = 0;
            foreach (File::files($logDir) as $file) {
                if ($file->getMTime() < time() - 7 * 24 * 60 * 60) {
                    File::delete($file);
                    $count++;
                }
            }
            $cleaned['logs'] = $count . ' files removed';
        }

        // Clean Android build
        $androidBuild = base_path('../frontend/android/build');
        if (File::exists($androidBuild)) {
            $size = $this->getDirectorySize($androidBuild);
            File::deleteDirectory($androidBuild);
            $cleaned['android_build'] = $size;
        }

        return $cleaned;
    }

    private function getDirectorySize($path): string
    {
        if (!File::exists($path)) {
            return '0 B';
        }

        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    private function formatBytes($bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    private function clearLaravelCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('optimize:clear');
    }

    /**
     * Build workspace
     */
    public function workspace(): array
    {
        $workspaceDir = '/tmp/kin-workspace-' . date('Ymd_His');
        
        // Create workspace
        File::makeDirectory($workspaceDir, 0755, true);
        
        $result = [
            'workspace' => $workspaceDir,
            'steps' => [],
        ];

        // Clone from GitHub
        $cloneResult = Process::run("git clone https://github.com/boifagusy/kin-platform.git {$workspaceDir}/repo 2>&1");
        $result['steps']['clone'] = $cloneResult->successful() ? '✅ Success' : '❌ Failed';

        if (!$cloneResult->successful()) {
            File::deleteDirectory($workspaceDir);
            return $result;
        }

        // Install dependencies
        $npmResult = Process::run("cd {$workspaceDir}/repo/frontend && npm install --legacy-peer-deps --no-bin-links 2>&1");
        $result['steps']['npm'] = $npmResult->successful() ? '✅ Success' : '❌ Failed';

        $composerResult = Process::run("cd {$workspaceDir}/repo/backend && composer install 2>&1");
        $result['steps']['composer'] = $composerResult->successful() ? '✅ Success' : '❌ Failed';

        // Build frontend
        $buildResult = Process::run("cd {$workspaceDir}/repo/frontend && npm run build 2>&1");
        $result['steps']['build'] = $buildResult->successful() ? '✅ Success' : '❌ Failed';

        // Build APK if frontend build succeeded
        if ($buildResult->successful()) {
            $capResult = Process::run("cd {$workspaceDir}/repo/frontend && npx cap sync android 2>&1");
            $result['steps']['capacitor'] = $capResult->successful() ? '✅ Success' : '❌ Failed';

            $gradleResult = Process::run("cd {$workspaceDir}/repo/frontend/android && ./gradlew assembleDebug 2>&1");
            $result['steps']['apk'] = $gradleResult->successful() ? '✅ Success' : '❌ Failed';

            if ($gradleResult->successful()) {
                $apkPath = "{$workspaceDir}/repo/frontend/android/app/build/outputs/apk/debug/app-debug.apk";
                if (File::exists($apkPath)) {
                    $result['apk'] = $apkPath;
                    $result['apk_size'] = $this->formatBytes(File::size($apkPath));
                }
            }
        }

        return $result;
    }
}
