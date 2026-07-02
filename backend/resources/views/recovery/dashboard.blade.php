@extends('layouts.admin')

@section('title', 'Recovery')

@section('content')
@php
    try {
        $engine = app()->make(\App\Services\Recovery\RecoveryEngine::class);
        $stats = $engine->getStats();
        
        $recent = \App\Models\RecoveryAttempt::with('action')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();
            
        $successCount = \App\Models\RecoveryAttempt::where('status', 'success')->count();
        $failedCount = \App\Models\RecoveryAttempt::where('status', 'failed')->count();
        $escalatedCount = \App\Models\RecoveryAttempt::where('escalated', true)->count();
        $runningCount = \App\Models\RecoveryAttempt::where('status', 'running')->count();
    } catch (\Exception $e) {
        $stats = ['total' => 0, 'success_rate' => 0];
        $recent = collect();
        $successCount = 0;
        $failedCount = 0;
        $escalatedCount = 0;
        $runningCount = 0;
    }
@endphp

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">🔄 Recovery</h1>
        <p class="text-gray-500 mt-1">Self-healing orchestration engine</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Total Attempts</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Success Rate</p>
            <p class="text-2xl font-bold text-{{ ($stats['success_rate'] ?? 0) >= 70 ? 'green-600' : (($stats['success_rate'] ?? 0) >= 40 ? 'yellow-600' : 'red-600') }}">
                {{ $stats['success_rate'] ?? 0 }}%
            </p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Successful</p>
            <p class="text-2xl font-bold text-green-600">{{ $successCount }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Failed</p>
            <p class="text-2xl font-bold text-red-600">{{ $failedCount }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Escalated</p>
            <p class="text-2xl font-bold text-orange-600">{{ $escalatedCount }}</p>
        </div>
    </div>

    <!-- Recent Attempts -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">📋 Recent Recovery Attempts</h2>
        @if($recent->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">ID</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Action</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Status</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Subsystem</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Escalated</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $attempt)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">#{{ $attempt->id }}</td>
                            <td class="py-3 px-4">{{ $attempt->action->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs text-white bg-{{ $attempt->status === 'success' ? 'green-500' : ($attempt->status === 'running' ? 'blue-500' : 'red-500') }}">
                                    {{ strtoupper($attempt->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">{{ $attempt->subsystem ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ $attempt->escalated ? '⚠️ Yes' : '—' }}</td>
                            <td class="py-3 px-4 text-gray-400">{{ $attempt->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No recovery attempts recorded yet.</p>
        @endif
    </div>
</div>
@endsection
