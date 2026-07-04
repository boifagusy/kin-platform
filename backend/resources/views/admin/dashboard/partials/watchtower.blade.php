@php
    try {
        // Get system health data from HealthService
        $healthService = app()->make(\App\Services\Watchtower\HealthService::class);
        $health = $healthService->getHealthStatus();
        
        $diskUsage = $health['disk_usage'] ?? ['used_percentage' => 0, 'total' => '0 GB', 'used' => '0 GB', 'free' => '0 GB'];
        $status = $health['disk_usage']['status'] ?? 'unknown';
        $database = $health['database'] ?? ['status' => 'unknown'];
        $cache = $health['cache'] ?? ['status' => 'unknown'];
        $storage = $health['storage'] ?? ['status' => 'unknown'];
        $queue = $health['queue'] ?? ['status' => 'unknown'];
        $memory = $health['memory'] ?? ['used_percentage' => 0];
        $cpu = $health['cpu'] ?? ['load_1min' => 0];
        
        $percent = round($diskUsage['used_percentage'] ?? 0, 1);
        $totalGB = $diskUsage['total'] ?? '0 GB';
        $usedGB = $diskUsage['used'] ?? '0 GB';
        $freeGB = $diskUsage['free'] ?? '0 GB';
        
        $overallStatus = $status === 'healthy' ? 'healthy' : ($status === 'warning' ? 'warning' : 'critical');
        $statusColor = $overallStatus === 'healthy' ? 'green' : ($overallStatus === 'warning' ? 'yellow' : 'red');
        $statusText = ucfirst($overallStatus);
        
        // Get uptime from API health
        $uptime = $health['api']['uptime'] ?? 'N/A';
        $healthScore = $health['health_score'] ?? 0;
        
    } catch (\Exception $e) {
        $percent = 0;
        $totalGB = '0 GB';
        $usedGB = '0 GB';
        $freeGB = '0 GB';
        $overallStatus = 'unknown';
        $statusColor = 'gray';
        $statusText = 'Unknown';
        $uptime = 'N/A';
        $healthScore = 0;
        $database = ['status' => 'unknown'];
        $cache = ['status' => 'unknown'];
        $storage = ['status' => 'unknown'];
        $queue = ['status' => 'unknown'];
    }
@endphp

<!-- System Health Widget -->
<div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm card-hover">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <span class="text-xl">🖥️</span> System Health
        </h2>
        <span class="text-xs px-2.5 py-1 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 font-medium">
            {{ $statusText }}
        </span>
    </div>
    
    <!-- Health Score -->
    <div class="mb-3">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Health Score</span>
            <span class="text-sm font-bold text-{{ $statusColor }}-600">{{ $healthScore }}%</span>
        </div>
        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden mt-1">
            <div class="h-full bg-{{ $statusColor }}-500 rounded-full transition-all" style="width: {{ min($healthScore, 100) }}%"></div>
        </div>
    </div>
    
    <div class="space-y-3">
        <!-- Storage -->
        <div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">💾 Storage</span>
                <span class="text-sm font-medium text-gray-800">{{ $usedGB }} / {{ $totalGB }}</span>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden mt-1">
                <div class="h-full bg-{{ $percent > 90 ? 'red' : ($percent > 75 ? 'yellow' : 'green') }}-500 rounded-full transition-all" style="width: {{ min($percent, 100) }}%"></div>
            </div>
            <div class="text-xs text-right text-gray-400 mt-0.5">{{ $percent }}% used</div>
        </div>
        
        <!-- Status Grid -->
        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
            <div>
                <span class="text-xs text-gray-500">Database</span>
                <p class="text-sm font-medium text-gray-700">
                    @if(isset($database['status']) && $database['status'] === 'healthy')
                        ✅ Healthy
                    @elseif(isset($database['status']) && $database['status'] === 'degraded')
                        ⚠️ Degraded
                    @else
                        ❌ Error
                    @endif
                </p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Cache</span>
                <p class="text-sm font-medium text-gray-700">
                    @if(isset($cache['status']) && $cache['status'] === 'healthy')
                        ✅ Healthy
                    @elseif(isset($cache['status']) && $cache['status'] === 'degraded')
                        ⚠️ Degraded
                    @else
                        ❌ Error
                    @endif
                </p>
            </div>
        </div>
        
        <!-- System Stats -->
        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
            <div>
                <span class="text-xs text-gray-500">⚡ Memory</span>
                <p class="text-sm font-medium text-gray-700">{{ $memory['used_percentage'] ?? 0 }}%</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">⏱️ Uptime</span>
                <p class="text-sm font-medium text-gray-700">{{ $uptime }}</p>
            </div>
        </div>
        
        <!-- View Details Link -->
        <div class="pt-3 border-t border-gray-100">
            <a href="{{ route('watchtower.overview') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 transition-colors">
                Click to view details
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>
</div>

<style>
.card-hover {
    transition: all 0.2s ease;
}
.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
}
</style>
