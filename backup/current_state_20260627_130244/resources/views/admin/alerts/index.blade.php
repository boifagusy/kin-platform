@extends('layouts.admin')

@section('title', 'Kin Alerts')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kin Alerts</h1>
        <p class="text-sm text-gray-500 mt-1">Monitor and manage safety alerts</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user or phone..." class="px-4 py-2 border border-gray-300 rounded-lg flex-1">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
            <select name="priority" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Priority</option>
                <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
            </select>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg">Filter</button>
            <a href="{{ route('admin.alerts.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg">Clear</a>
        </form>
    </div>

    <!-- Alerts Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100">
                        <td class="px-6 py-4 text-sm">#{{ $alert->id }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ $alert->user->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $alert->user->phone ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                {{ strtoupper($alert->escalation_type ?? 'SOS') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($alert->priority == 'critical') bg-red-100 text-red-800
                                @elseif($alert->priority == 'high') bg-orange-100 text-orange-800
                                @elseif($alert->priority == 'medium') bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ strtoupper($alert->priority ?? 'LOW') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($alert->status == 'resolved') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ strtoupper($alert->status ?? 'ACTIVE') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $alert->assignedAdmin->name ?? 'Unassigned' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $alert->created_at->format('M d, H:i') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.alerts.show', $alert->id) }}" class="text-primary hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">No alerts found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $alerts->links() }}
        </div>
    </div>
</div>
@endsection
