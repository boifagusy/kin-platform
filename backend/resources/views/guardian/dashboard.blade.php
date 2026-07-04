@extends('layouts.admin')

@section('title', 'Guardian Platform')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🛡️ Guardian Platform</h1>
        <p class="text-sm text-gray-500 mt-1">Unified view of all KIN subsystems</p>
    </div>

    <!-- Guardian Score Card -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl p-6 sm:p-8 mb-8 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <p class="text-purple-200 text-sm font-medium">Overall Guardian Score</p>
                <p class="text-5xl sm:text-6xl font-bold mt-1">{{ $guardianScore['overall'] ?? 0 }}</p>
                <div class="flex gap-4 mt-3">
                    <div>
                        <p class="text-purple-200 text-xs">Operations</p>
                        <p class="text-lg font-semibold">{{ $guardianScore['operations'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-purple-200 text-xs">Security</p>
                        <p class="text-lg font-semibold">{{ $guardianScore['security'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-purple-200 text-xs">Safety</p>
                        <p class="text-lg font-semibold">{{ $guardianScore['safety'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 sm:mt-0 text-right">
                <span class="text-purple-200 text-sm">Status</span>
                <p class="text-xl font-semibold">
                    @if(($guardianScore['overall'] ?? 0) >= 80)
                        <span class="text-green-300">✅ Excellent</span>
                    @elseif(($guardianScore['overall'] ?? 0) >= 60)
                        <span class="text-yellow-300">⚠️ Good</span>
                    @else
                        <span class="text-red-300">🚨 Critical</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $platformStatus['total_users'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Active Incidents</p>
            <p class="text-2xl font-bold text-{{ ($platformStatus['incidents']['critical'] ?? 0) > 0 ? 'red-600' : 'green-600' }} mt-1">
                {{ $platformStatus['incidents']['total'] ?? 0 }}
            </p>
            @if(($platformStatus['incidents']['critical'] ?? 0) > 0)
                <p class="text-xs text-red-500">Critical: {{ $platformStatus['incidents']['critical'] }}</p>
            @endif
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Security Status</p>
            <p class="text-2xl font-bold text-{{ ($platformStatus['security']['status'] ?? 'normal') === 'normal' ? 'green-600' : 'yellow-600' }} mt-1">
                {{ ucfirst($platformStatus['security']['status'] ?? 'Normal') }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Safety Status</p>
            <p class="text-2xl font-bold text-{{ ($platformStatus['safety']['status'] ?? 'normal') === 'normal' ? 'green-600' : 'yellow-600' }} mt-1">
                {{ ucfirst($platformStatus['safety']['status'] ?? 'Normal') }}
            </p>
        </div>
    </div>

    <!-- Platform Health -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                    <span class="material-symbols-outlined">check_circle</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Watchtower</p>
                    <p class="font-semibold text-green-700">{{ ucfirst($platformStatus['health']['status'] ?? 'Healthy') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <span class="material-symbols-outlined">lock</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Sentinel</p>
                    <p class="font-semibold text-blue-700">{{ ucfirst($platformStatus['security']['status'] ?? 'Normal') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                    <span class="material-symbols-outlined">favorite</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pulse</p>
                    <p class="font-semibold text-orange-700">{{ ucfirst($platformStatus['safety']['status'] ?? 'Normal') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">📋 Unified Timeline</h3>
        </div>
        <div class="p-4 max-h-96 overflow-y-auto">
            @if(empty($timeline))
                <p class="text-gray-500 text-center py-8">No events yet</p>
            @else
                <div class="space-y-3">
                    @foreach($timeline as $event)
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors border border-gray-100">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($event['source'] === 'watchtower')
                                    <span class="text-cyan-500">👁️</span>
                                @elseif($event['source'] === 'sentinel')
                                    <span class="text-red-500">🔒</span>
                                @elseif($event['source'] === 'pulse')
                                    <span class="text-green-500">💓</span>
                                @else
                                    <span class="text-gray-400">📌</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-sm font-medium text-gray-800">{{ $event['message'] }}</span>
                                    <span class="text-xs text-gray-400">{{ $event['time_ago'] }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span class="text-xs text-gray-500">User: {{ $event['user'] }}</span>
                                    <span class="text-xs text-gray-400">|</span>
                                    <span class="text-xs text-gray-500">Type: {{ $event['event_type'] }}</span>
                                    @if($event['severity'] !== 'info')
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $event['severity'] === 'critical' ? 'red' : ($event['severity'] === 'high' ? 'orange' : 'yellow') }}-100 text-{{ $event['severity'] === 'critical' ? 'red' : ($event['severity'] === 'high' ? 'orange' : 'yellow') }}-700">
                                            {{ ucfirst($event['severity']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
