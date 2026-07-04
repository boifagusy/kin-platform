@extends('layouts.admin')

@section('title', 'Watchtower Overview')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">👁️ Watchtower</h1>
        <p class="text-sm text-gray-500 mt-1">Operations monitoring and incident management</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Total Incidents</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $total_incidents ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Critical</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $critical_incidents ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Open</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $open_incidents ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Resolved (24h)</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $resolved_incidents ?? 0 }}</p>
        </div>
    </div>

    <!-- Recent Incidents -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">📋 Recent Incidents</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Severity</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_incidents ?? [] as $incident)
                        <tr class="border-t border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-700">{{ $incident->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $incident->severity === 'critical' ? 'bg-red-100 text-red-800' :
                                       ($incident->severity === 'high' ? 'bg-orange-100 text-orange-800' :
                                       ($incident->severity === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($incident->severity ?? 'Low') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $incident->status === 'resolved' ? 'bg-green-100 text-green-800' :
                                       ($incident->status === 'open' ? 'bg-red-100 text-red-800' :
                                       ($incident->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($incident->status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $incident->created_at ? $incident->created_at->diffForHumans() : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No incidents recorded</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
