@extends('layouts.admin')

@section('title', 'Sentinel Security Dashboard')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🔒 Sentinel Security</h1>
        <p class="text-sm text-gray-500 mt-1">Security monitoring and threat detection</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Total Events</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $total_events ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Critical</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $critical_events ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">High Risk Users</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">{{ $high_risk_users ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $total_users ?? 0 }}</p>
        </div>
    </div>

    <!-- Recent Security Events -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">📋 Recent Security Events</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Severity</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_events ?? [] as $event)
                        <tr class="border-t border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-700">{{ str_replace('_', ' ', $event->event_type ?? 'Unknown') }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $event->user->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $event->severity === 'critical' ? 'bg-red-100 text-red-800' :
                                       ($event->severity === 'high' ? 'bg-orange-100 text-orange-800' :
                                       ($event->severity === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($event->severity ?? 'Info') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $event->created_at ? $event->created_at->diffForHumans() : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No security events recorded</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
