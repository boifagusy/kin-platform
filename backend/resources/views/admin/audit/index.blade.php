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
            <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['this_month'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">This Month</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-sm text-gray-600 block mb-1">Action</label>
                <select name="action" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Actions</option>
                    @foreach($actions ?? [] as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ $action }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-600 block mb-1">User</label>
                <select name="user" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.audit.index') }}" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors inline-block">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Log Entries -->
    <div class="bg-white rounded-xl p-6 border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h2>
        
        @if(isset($logs) && $logs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200">
                            <th class="pb-2 font-semibold text-gray-600">User</th>
                            <th class="pb-2 font-semibold text-gray-600">Action</th>
                            <th class="pb-2 font-semibold text-gray-600">Details</th>
                            <th class="pb-2 font-semibold text-gray-600">IP Address</th>
                            <th class="pb-2 font-semibold text-gray-600">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3">
                                    {{ $log->adminUser?->name ?? $log->adminUser?->email ?? 'Unknown' }}
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-600 max-w-xs truncate">
                                    {{ $log->details ?? $log->entity_type ?? '-' }}
                                </td>
                                <td class="py-3 text-gray-500 text-xs">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td class="py-3 text-gray-500 text-xs">
                                    {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                No audit logs found.
            </div>
        @endif
    </div>
</div>
@endsection
