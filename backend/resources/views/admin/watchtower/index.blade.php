@extends('layouts.admin')

@section('title', 'Watchtower — System Monitoring')

@section('content')
<div class="mb-6 md:mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-1">Watchtower</h1>
            <p class="text-sm sm:text-base text-gray-500">Real-time system monitoring and operational health.</p>
        </div>
        <div class="text-right text-xs text-gray-400">
            Updated: {{ \Carbon\Carbon::parse($lastUpdated)->diffForHumans() }}
        </div>
    </div>

    {{-- Status Summary Bar --}}
    <div class="flex gap-4 mt-4 text-sm">
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
            <span class="text-gray-600">{{ $healthyCount }} Healthy</span>
        </span>
        @if($warningCount > 0)
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
            <span class="text-gray-600">{{ $warningCount }} Warning</span>
        </span>
        @endif
        @if($criticalCount > 0)
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
            <span class="text-gray-600">{{ $criticalCount }} Critical</span>
        </span>
        @endif
        @if($errorCount > 0)
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
            <span class="text-gray-600">{{ $errorCount }} Error</span>
        </span>
        @endif
    </div>
</div>

{{-- Module Cards Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
    @foreach($modules as $module)
    <div class="bg-white rounded-xl border shadow-sm min-h-48
        @if($module['health'] === 'critical')
            border-l-4 border-l-red-500 border-red-200
        @elseif($module['health'] === 'warning')
            border-l-4 border-l-yellow-500 border-yellow-200
        @elseif($module['health'] === 'error')
            border-red-300 bg-red-50
        @else
            border-gray-200
        @endif
    ">
        <div class="p-4 sm:p-5">
            {{-- Card Header --}}
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-xl">
                        {{ $module['icon'] }}
                    </span>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $module['label'] }}</h3>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full
                    @if($module['health'] === 'healthy')
                        bg-green-50 text-green-700
                    @elseif($module['health'] === 'warning')
                        bg-yellow-50 text-yellow-700
                    @elseif($module['health'] === 'critical')
                        bg-red-50 text-red-700
                    @else
                        bg-gray-100 text-gray-600
                    @endif
                ">
                    {{ ucfirst($module['health']) }}
                </span>
            </div>

            {{-- Module Content --}}
            @if($module['health'] === 'error')
                <div class="text-center py-4">
                    <span class="material-symbols-outlined text-gray-400 text-3xl mb-2">warning</span>
                    <p class="text-sm text-gray-500">{{ $module['error'] ?? 'Unavailable' }}</p>
                </div>
            @elseif(empty($module['items']))
                <div class="text-center py-4">
                    <p class="text-sm text-gray-400">No metrics available</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($module['items'] as $item)
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs text-gray-500 truncate mr-2">
                            {{ ucwords(str_replace('_', ' ', $item['label'])) }}
                        </span>
                        <span class="text-sm font-semibold text-gray-800 text-right whitespace-nowrap">
                            @if(is_array($item['value']))
                                {{ json_encode($item['value']) }}
                            @elseif(is_numeric($item['value']))
                                {{ is_float($item['value']) ? number_format($item['value'], 1) : number_format($item['value']) }}
                            @else
                                {{ $item['value'] }}
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Empty State (only if all modules errored) --}}
@if($errorCount === count($modules))
<div class="text-center py-12">
    <span class="material-symbols-outlined text-gray-300 text-5xl mb-4">cloud_off</span>
    <h3 class="text-lg font-semibold text-gray-600">All modules unavailable</h3>
    <p class="text-sm text-gray-400 mt-1">The monitoring services could not be reached. Please check the system status.</p>
</div>
@endif
@endsection
