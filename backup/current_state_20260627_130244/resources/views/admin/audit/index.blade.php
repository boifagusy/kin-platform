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
            <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</div>
            <div class="text-xs text-gray-500">Total Actions</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['today']) }}</div>
            <div class="text-xs text-gray-500">Today</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['this_week']) }}</div>
            <div class="text-xs text-gray-500">This Week</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-primary">{{ number_format(count($stats['actions_by_type'])) }}</div>
            <div class="text-xs text-gray-500">Action Types</div>
        </div>
    </div>

    <!-- Top Actions Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Top Actions</h3>
        <div class="flex flex-wrap gap-3">
            @foreach($stats['actions_by_type'] as $action)
            <span class="px-3 py-1 bg-gray-100 rounded-full text-xs text-gray-600">
                {{ is_array($action) ? ($action['action'] ?? 'Unknown') : $action->action }} 
                ({{ is_array($action) ? ($action['count'] ?? 0) : $action->count }})
            </span>
            @endforeach
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            </div>
            <select name="action_type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Actions</option>
                @foreach($actionTypes as $type)
                <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <select name="admin_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Admins</option>
                @foreach($admins as $admin)
                <option value="{{ $admin['id'] }}" {{ request('admin_id') == $admin['id'] ? 'selected' : '' }}>{{ $admin['name'] }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary/90 transition-colors">Apply Filters</button>
                <a href="{{ route('admin.audit.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $log->created_at->format('M d, H:i:s') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold">
                                    {{ strtoupper(substr($log->admin->name ?? 'S', 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $log->admin->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if(str_contains($log->action, 'LOGIN')) bg-blue-100 text-blue-800
                                @elseif(str_contains($log->action, 'SUSPEND')) bg-red-100 text-red-800
                                @elseif(str_contains($log->action, 'ACTIVATE')) bg-green-100 text-green-800
                                @elseif(str_contains($log->action, 'SETTING')) bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $log->entity_type }} 
                            @if($log->entity_id)
                            <span class="text-xs text-gray-400">#{{ $log->entity_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                            @php
                                $oldValues = is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values;
                                $newValues = is_string($log->new_values) ? json_decode($log->new_values, true) : $log->new_values;
                            @endphp
                            @if($oldValues || $newValues)
                                Changed
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono text-xs">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No audit logs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
