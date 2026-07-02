@php
    try {
        $guardianService = app()->make(\App\Services\Guardian\GuardianAggregationService::class);
        $score = $guardianService->getGuardianScore();
        $platformStatus = $guardianService->getPlatformStatus();
        
        $recoveryEngine = app()->make(\App\Services\Recovery\RecoveryEngine::class);
        $recoveryStats = $recoveryEngine->getStats();
    } catch (\Exception $e) {
        $score = ['overall' => 0, 'operations' => 0, 'security' => 0, 'safety' => 0];
        $platformStatus = ['health' => ['score' => 0], 'security' => ['score' => 0], 'safety' => ['score' => 0]];
        $recoveryStats = ['success_rate' => 0];
    }
@endphp

<div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm card-hover">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">🛡️ Platform Health</h2>
    <div class="space-y-3">
        <!-- Guardian -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <div class="flex items-center gap-3">
                <span class="text-purple-600 font-semibold text-sm">Guardian</span>
                <span class="text-[10px] font-medium text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">v0.1</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-purple-600">{{ $score['overall'] ?? 0 }}%</span>
                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-purple-600 rounded-full" style="width: {{ $score['overall'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
        
        <!-- Watchtower -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <div class="flex items-center gap-3">
                <span class="text-cyan-600 font-semibold text-sm">Watchtower</span>
                <span class="text-[10px] font-medium text-cyan-600 bg-cyan-50 px-1.5 py-0.5 rounded">v2.0</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-cyan-600">{{ $platformStatus['health']['score'] ?? 0 }}%</span>
                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-cyan-600 rounded-full" style="width: {{ $platformStatus['health']['score'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
        
        <!-- Sentinel -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <div class="flex items-center gap-3">
                <span class="text-red-600 font-semibold text-sm">Sentinel</span>
                <span class="text-[10px] font-medium text-red-600 bg-red-50 px-1.5 py-0.5 rounded">v0.5</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-red-600">{{ $platformStatus['security']['score'] ?? 0 }}%</span>
                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-red-600 rounded-full" style="width: {{ $platformStatus['security']['score'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
        
        <!-- Pulse -->
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <div class="flex items-center gap-3">
                <span class="text-green-600 font-semibold text-sm">Pulse</span>
                <span class="text-[10px] font-medium text-green-600 bg-green-50 px-1.5 py-0.5 rounded">v0.4</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-green-600">{{ $platformStatus['safety']['score'] ?? 0 }}%</span>
                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-green-600 rounded-full" style="width: {{ $platformStatus['safety']['score'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
        
        <!-- Recovery -->
        <div class="flex items-center justify-between py-2">
            <div class="flex items-center gap-3">
                <span class="text-yellow-600 font-semibold text-sm">Recovery</span>
                <span class="text-[10px] font-medium text-yellow-600 bg-yellow-50 px-1.5 py-0.5 rounded">v1.0</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-yellow-600">{{ $recoveryStats['success_rate'] ?? 0 }}%</span>
                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-yellow-600 rounded-full" style="width: {{ $recoveryStats['success_rate'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
