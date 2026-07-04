@extends('layouts.admin')

@section('title', 'Audit Center')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Audit Center</h1>
            <p class="text-sm text-gray-500 mt-1">View and monitor all admin actions and system events</p>
        </div>
        <a href="{{ route('admin.audit.export', request()->query()) }}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2 justify-center">
            <span class="material-symbols-outlined text-sm">download</span>
            Export CSV
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['total'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Total Actions</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['today'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Today</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['this_week'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">This Week</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-primary">{{ number_format(count($stats['actions_by_type'] ?? [])) }}</div>
            <div class="text-xs text-gray-500">Action Types</div>
        </div>
    </div>

    <!-- Top Actions -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Top Actions</h3>
        <div class="space-y-2">
            @foreach($stats['actions_by_type'] ?? [] as $action => $count)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $action)) }}</span>
                    <span class="text-sm font-medium text-gray-800">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Top Users -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Top Users</h3>
        <div class="space-y-2">
            @foreach($stats['top_users'] ?? [] as $user)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-600">{{ $user['name'] ?? 'Unknown' }}</span>
                    <span class="text-sm font-medium text-gray-800">{{ $user['count'] ?? 0 }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700">Recent Activity</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Entity</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs ?? [] as $log)
                        <tr class="border-t border-gray-50">
                            <td class="px-4 py-3 text-gray-700">{{ $log->adminUser->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $log->entity_type ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
