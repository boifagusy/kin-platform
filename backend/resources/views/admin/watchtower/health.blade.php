@extends('layouts.admin')

@section('title', 'System Health')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🖥️ System Health</h1>
                <p class="text-sm text-gray-500 mt-1">Comprehensive system health monitoring</p>
            </div>
            <div>
                <span class="text-xs px-3 py-1.5 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 font-medium">
                    {{ $statusText }}
                </span>
                <span class="ml-2 text-xs text-gray-400">Health Score: {{ $healthScore }}%</span>
            </div>
        </div>
    </div>

    <!-- Health Score Bar -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Overall Health Score</span>
            <span class="text-xl font-bold text-{{ $statusColor }}-600">{{ $healthScore }}%</span>
        </div>
        <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-{{ $statusColor }}-500 rounded-full transition-all" style="width: {{ min($healthScore, 100) }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>0%</span>
            <span>50%</span>
            <span>100%</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <!-- Disk Usage -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">💾 Disk Usage</h3>
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-gray-600">Used: {{ $diskUsage['used'] ?? 'N/A' }}</span>
                <span class="text-sm text-gray-600">Free: {{ $diskUsage['free'] ?? 'N/A' }}</span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-{{ ($diskUsage['used_percentage'] ?? 0) > 90 ? 'red' : (($diskUsage['used_percentage'] ?? 0) > 75 ? 'yellow' : 'green') }}-500 rounded-full transition-all" style="width: {{ min($diskUsage['used_percentage'] ?? 0, 100) }}%"></div>
            </div>
            <div class="text-xs text-right text-gray-400 mt-1">{{ $diskUsage['used_percentage'] ?? 0 }}% used</div>
            <div class="text-xs text-gray-500 mt-2">Total: {{ $diskUsage['total'] ?? 'N/A' }}</div>
        </div>

        <!-- Database -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🗄️ Database</h3>
            <div class="flex items-center gap-2">
                @if(isset($database['status']) && $database['status'] === 'healthy')
                    <span class="text-2xl text-green-500">✅</span>
                    <span class="text-sm font-medium text-green-700">Healthy</span>
                @elseif(isset($database['status']) && $database['status'] === 'degraded')
                    <span class="text-2xl text-yellow-500">⚠️</span>
                    <span class="text-sm font-medium text-yellow-700">Degraded</span>
                @else
                    <span class="text-2xl text-red-500">❌</span>
                    <span class="text-sm font-medium text-red-700">Error</span>
                @endif
            </div>
            @if(isset($database['connection']))
                <div class="text-xs text-gray-500 mt-2">Connection: {{ $database['connection'] }}</div>
            @endif
            @if(isset($database['error']))
                <div class="text-xs text-red-500 mt-2">{{ $database['error'] }}</div>
            @endif
        </div>

        <!-- Cache -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">⚡ Cache</h3>
            <div class="flex items-center gap-2">
                @if(isset($cache['status']) && $cache['status'] === 'healthy')
                    <span class="text-2xl text-green-500">✅</span>
                    <span class="text-sm font-medium text-green-700">Healthy</span>
                @elseif(isset($cache['status']) && $cache['status'] === 'degraded')
                    <span class="text-2xl text-yellow-500">⚠️</span>
                    <span class="text-sm font-medium text-yellow-700">Degraded</span>
                @else
                    <span class="text-2xl text-red-500">❌</span>
                    <span class="text-sm font-medium text-red-700">Error</span>
                @endif
            </div>
            @if(isset($cache['driver']))
                <div class="text-xs text-gray-500 mt-2">Driver: {{ $cache['driver'] }}</div>
            @endif
            @if(isset($cache['error']))
                <div class="text-xs text-red-500 mt-2">{{ $cache['error'] }}</div>
            @endif
        </div>

        <!-- Queue -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">📋 Queue</h3>
            <div class="flex items-center gap-2">
                @if(isset($queue['status']) && $queue['status'] === 'healthy')
                    <span class="text-2xl text-green-500">✅</span>
                    <span class="text-sm font-medium text-green-700">Healthy</span>
                @elseif(isset($queue['status']) && $queue['status'] === 'degraded')
                    <span class="text-2xl text-yellow-500">⚠️</span>
                    <span class="text-sm font-medium text-yellow-700">Degraded</span>
                @else
                    <span class="text-2xl text-red-500">❌</span>
                    <span class="text-sm font-medium text-red-700">Error</span>
                @endif
            </div>
            @if(isset($queue['connection']))
                <div class="text-xs text-gray-500 mt-2">Connection: {{ $queue['connection'] }}</div>
            @endif
        </div>

        <!-- Memory -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🧠 Memory</h3>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Used: {{ $memory['current'] ?? 'N/A' }}</span>
                <span class="text-gray-600">Limit: {{ $memory['limit'] ?? 'N/A' }}</span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden mt-1">
                <div class="h-full bg-{{ ($memory['used_percentage'] ?? 0) > 90 ? 'red' : (($memory['used_percentage'] ?? 0) > 75 ? 'yellow' : 'green') }}-500 rounded-full transition-all" style="width: {{ min($memory['used_percentage'] ?? 0, 100) }}%"></div>
            </div>
            <div class="text-xs text-right text-gray-400 mt-1">{{ $memory['used_percentage'] ?? 0 }}% used</div>
        </div>

        <!-- CPU -->
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">⚙️ CPU</h3>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div>
                    <div class="text-sm font-bold text-gray-800">{{ number_format($cpu['load_1min'] ?? 0, 2) }}</div>
                    <div class="text-xs text-gray-500">1 min</div>
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-800">{{ number_format($cpu['load_5min'] ?? 0, 2) }}</div>
                    <div class="text-xs text-gray-500">5 min</div>
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-800">{{ number_format($cpu['load_15min'] ?? 0, 2) }}</div>
                    <div class="text-xs text-gray-500">15 min</div>
                </div>
            </div>
            <div class="text-xs text-gray-400 text-center mt-2">CPUs: {{ $cpu['cpus'] ?? 1 }}</div>
        </div>
    </div>

    <!-- Back to Watchtower -->
    <div class="mt-6">
        <a href="{{ route('watchtower.overview') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Back to Watchtower
        </a>
    </div>
</div>
@endsection
