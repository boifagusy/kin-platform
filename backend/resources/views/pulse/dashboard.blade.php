@extends('layouts.admin')

@section('title', 'Pulse Safety Dashboard')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">💓 Pulse Safety</h1>
        <p class="text-sm text-gray-500 mt-1">Real-time safety intelligence for all users</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Average Score</p>
            <p class="text-2xl font-bold text-{{ $avgScore >= 80 ? 'green-600' : ($avgScore >= 50 ? 'yellow-600' : 'red-600') }} mt-1">
                {{ $avgScore ?? 0 }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Users Monitored</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalUsers ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Active Emergencies</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $emergencyCount ?? 0 }}</p>
            @if($emergencyCount > 0)
                <p class="text-xs text-red-500">🚨 Immediate attention needed</p>
            @endif
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">At Risk Users</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">{{ $atRiskCount ?? 0 }}</p>
            @if($atRiskCount > 0)
                <p class="text-xs text-orange-500">⚠️ Monitor closely</p>
            @endif
        </div>
    </div>

    <!-- User Safety Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">👤 User Safety Status</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Level</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Trend</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Factors</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userScores ?? [] as $data)
                        <tr class="border-t border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-700">{{ $data['user']->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $data['level_config']['color'] ?? 'gray' }}-100 text-{{ $data['level_config']['color'] ?? 'gray' }}-800">
                                    {{ $data['score'] ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm">
                                    {{ $data['level_config']['icon'] ?? '🟢' }}
                                    {{ $data['level_config']['label'] ?? 'Safe' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if(($data['trend'] ?? 'stable') === 'improving')
                                    <span class="text-green-600">↑ Improving</span>
                                @elseif(($data['trend'] ?? 'stable') === 'declining')
                                    <span class="text-red-600">↓ Declining</span>
                                @else
                                    <span class="text-gray-400">→ Stable</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($data['factors'] ?? [] as $factor)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                            {{ $factor['label'] ?? 'Unknown' }}
                                            <span class="ml-1 text-red-500">-{{ $factor['impact'] ?? 0 }}</span>
                                        </span>
                                    @endforeach
                                    @if(empty($data['factors']))
                                        <span class="text-xs text-gray-400">No active factors</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Safety Events -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">📋 Recent Safety Events</h3>
        </div>
        <div class="p-4">
            @if($recentEvents && $recentEvents->count() > 0)
                <div class="space-y-2">
                    @foreach($recentEvents as $event)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-gray-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">
                                    @if($event->event_type === 'missed_checkin')
                                        ⏰
                                    @elseif($event->event_type === 'sos_triggered')
                                        🚨
                                    @elseif($event->event_type === 'low_battery')
                                        🔋
                                    @else
                                        📌
                                    @endif
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">{{ str_replace('_', ' ', $event->event_type ?? 'Unknown') }}</p>
                                    <p class="text-xs text-gray-500">User: {{ $event->user->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400">{{ $event->created_at ? $event->created_at->diffForHumans() : 'Unknown' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No recent safety events</p>
            @endif
        </div>
    </div>
</div>
@endsection
