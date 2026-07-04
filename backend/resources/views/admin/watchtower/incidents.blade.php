@extends('layouts.admin')

@section('title', 'Watchtower Incidents')

@section('content')
<div class="p-4 sm:p-6">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">📋 Incidents</h1>
        <p class="text-sm text-gray-500 mt-1">View and manage all incidents</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">All Incidents</h3>
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
                    @forelse($incidents ?? [] as $incident)
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
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No incidents found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
