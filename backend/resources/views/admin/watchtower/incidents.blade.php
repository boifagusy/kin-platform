@extends('layouts.admin')

@section('title', 'Incident Management')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">🚨 Incident Management</h1>
    <p class="text-gray-500">View and manage all system incidents</p>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-blue-500">{{ $stats['total'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Total Incidents</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-red-500">{{ $stats['critical'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Critical</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-yellow-500">{{ $stats['open'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Open</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-green-500">{{ $stats['resolved'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Resolved</div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl p-4 border border-gray-200 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-sm text-gray-600 block mb-1">Status</label>
            <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                <option value="investigating" {{ request('status') == 'investigating' ? 'selected' : '' }}>Investigating</option>
                <option value="acknowledged" {{ request('status') == 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                <option value="mitigated" {{ request('status') == 'mitigated' ? 'selected' : '' }}>Mitigated</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
        <div>
            <label class="text-sm text-gray-600 block mb-1">Severity</label>
            <select name="severity" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">All Severity</option>
                <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>Info</option>
            </select>
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Filter
            </button>
            <a href="{{ route('admin.watchtower.incidents') }}" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors inline-block">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Incident List -->
<div class="bg-white rounded-xl p-6 border border-gray-200">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">All Incidents</h2>
    <div class="space-y-2">
        @forelse($incidents ?? [] as $incident)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border-l-4 border-{{ $incident->severity === 'critical' ? 'red' : ($incident->severity === 'high' ? 'orange' : ($incident->severity === 'medium' ? 'yellow' : 'blue')) }}-500">
                <div class="flex-1">
                    <div class="font-medium text-gray-800">{{ $incident->title ?? 'Untitled' }}</div>
                    <div class="text-xs text-gray-500">{{ $incident->source ?? 'Unknown' }} • {{ $incident->detected_at ? \Carbon\Carbon::parse($incident->detected_at)->format('Y-m-d H:i:s') : 'N/A' }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2 py-1 text-xs rounded {{ $incident->severity === 'critical' ? 'bg-red-100 text-red-700' : ($incident->severity === 'high' ? 'bg-orange-100 text-orange-700' : ($incident->severity === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700')) }}">
                        {{ $incident->severity ?? 'info' }}
                    </span>
                    <span class="px-2 py-1 text-xs rounded {{ $incident->status === 'new' ? 'bg-red-100 text-red-700' : ($incident->status === 'investigating' ? 'bg-yellow-100 text-yellow-700' : ($incident->status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')) }}">
                        {{ $incident->status ?? 'new' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-gray-500 text-sm text-center py-8">✅ No incidents found</div>
        @endforelse
    </div>
</div>
@endsection
