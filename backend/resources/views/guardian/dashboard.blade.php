@extends('layouts.admin')

@section('title', 'Guardian Platform')

@section('content')
@php
    try {
        $guardianService = app()->make(\App\Services\Guardian\GuardianAggregationService::class);
        $status = $guardianService->getPlatformStatus();
        $score = $guardianService->getGuardianScore();
        
        $totalUsers = \App\Models\User::count();
        $activeIncidents = \App\Models\WatchtowerIncident::where('status', '!=', 'resolved')->count();
        $criticalIncidents = \App\Models\WatchtowerIncident::where('severity', 'critical')->where('status', '!=', 'resolved')->count();
        $safetyEvents = \App\Models\SafetyEvent::where('created_at', '>=', now()->subHours(24))->count();
        $securityEvents = \App\Models\SecurityEvent::where('created_at', '>=', now()->subHours(24))->count();
    } catch (\Exception $e) {
        $score = ['overall' => 0, 'operations' => 0, 'security' => 0, 'safety' => 0];
        $status = ['health' => ['status' => 'unknown'], 'incidents' => ['total' => 0]];
        $totalUsers = 0;
        $activeIncidents = 0;
        $criticalIncidents = 0;
        $safetyEvents = 0;
        $securityEvents = 0;
    }
@endphp

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">🛡️ Guardian Platform</h1>
        <p class="text-gray-500 mt-1">Unified view of all KIN subsystems</p>
    </div>

    <!-- Guardian Score -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-xl p-6 mb-8 text-white">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-purple-200 text-sm">Overall Guardian Score</p>
                <p class="text-5xl font-bold">{{ $score['overall'] ?? 0 }}</p>
                <p class="text-purple-200 text-sm mt-1">
                    Operations: {{ $score['operations'] ?? 0 }} | 
                    Security: {{ $score['security'] ?? 0 }} | 
                    Safety: {{ $score['safety'] ?? 0 }}
                </p>
            </div>
            <div class="text-right">
                <span class="text-purple-200 text-sm">Status</span>
                <p class="text-xl font-semibold">
                    @if(($score['overall'] ?? 0) >= 80)
                        <span class="text-green-300">✅ Excellent</span>
                    @elseif(($score['overall'] ?? 0) >= 60)
                        <span class="text-yellow-300">⚠️ Good</span>
                    @else
                        <span class="text-red-300">🔴 Critical</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Total Users</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Active Incidents</p>
            <p class="text-2xl font-bold text-{{ $activeIncidents > 0 ? 'red-600' : 'green-600' }}">{{ $activeIncidents }}</p>
            @if($criticalIncidents > 0)
                <p class="text-red-500 text-xs">Critical: {{ $criticalIncidents }}</p>
            @endif
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">24h Safety Events</p>
            <p class="text-2xl font-bold text-orange-600">{{ $safetyEvents }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">24h Security Events</p>
            <p class="text-2xl font-bold text-blue-600">{{ $securityEvents }}</p>
        </div>
    </div>

    <!-- Platform Health -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">📊 Platform Health</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg">
                <span class="text-2xl">🟢</span>
                <div>
                    <p class="text-sm text-gray-600">Watchtower</p>
                    <p class="font-semibold text-green-700">{{ $status['health']['status'] ?? 'Healthy' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                <span class="text-2xl">🔵</span>
                <div>
                    <p class="text-sm text-gray-600">Sentinel</p>
                    <p class="font-semibold text-blue-700">{{ $status['security']['status'] ?? 'Normal' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-orange-50 rounded-lg">
                <span class="text-2xl">🟠</span>
                <div>
                    <p class="text-sm text-gray-600">Pulse</p>
                    <p class="font-semibold text-orange-700">{{ $status['safety']['status'] ?? 'Normal' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="/admin/watchtower/overview" class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition text-center">
            <span class="text-2xl block">👁️</span>
            <span class="text-gray-700 font-medium">View Watchtower</span>
        </a>
        <a href="/admin/sentinel/dashboard" class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition text-center">
            <span class="text-2xl block">🔒</span>
            <span class="text-gray-700 font-medium">View Sentinel</span>
        </a>
        <a href="/pulse/dashboard" class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition text-center">
            <span class="text-2xl block">💓</span>
            <span class="text-gray-700 font-medium">View Pulse</span>
        </a>
    </div>
</div>
@endsection
