@extends('layouts.admin')

@section('title', 'Recovery Dashboard')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🔄 Recovery Engine</h1>
        <p class="text-sm text-gray-500 mt-1">Self-healing orchestration and automated recovery</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Total Attempts</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Success Rate</p>
            <p class="text-2xl font-bold text-{{ ($stats['success_rate'] ?? 0) >= 70 ? 'green-600' : ($stats['success_rate'] >= 40 ? 'yellow-600' : 'red-600') }} mt-1">
                {{ $stats['success_rate'] ?? 0 }}%
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Failed</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['failed'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wider">Escalated</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['escalated'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Recent Recovery Attempts -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">📋 Recent Recovery Attempts</h3>
            <span class="text-xs text-gray-400">Last {{ $recent->count() ?? 0 }} attempts</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Subsystem</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Escalated</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent ?? [] as $attempt)
                        <tr class="border-t border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-700 font-mono text-xs">#{{ $attempt->id }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $attempt->action->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $attempt->status === 'success' ? 'bg-green-100 text-green-800' : 
                                       ($attempt->status === 'running' ? 'bg-blue-100 text-blue-800' : 
                                       ($attempt->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($attempt->status ?? 'Unknown') }}
                                </span>
                                @if($attempt->status === 'success')
                                    <span class="ml-1 text-green-500">✅</span>
                                @elseif($attempt->status === 'failed')
                                    <span class="ml-1 text-red-500">❌</span>
                                @elseif($attempt->status === 'running')
                                    <span class="ml-1 text-blue-500">⏳</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $attempt->subsystem ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                @if($attempt->escalated)
                                    <span class="text-orange-600 font-medium">⚠️ Yes</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $attempt->created_at ? $attempt->created_at->diffForHumans() : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No recovery attempts recorded yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
