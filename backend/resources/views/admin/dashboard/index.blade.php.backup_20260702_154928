@extends('layouts.admin')

@section('title', 'System Overview')

@section('content')
    <div class="mb-6 md:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-1">System Overview</h1>
        <p class="text-sm sm:text-base text-gray-500">Real-time metrics and system health monitoring.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6 mb-8">
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-200 shadow-sm card-hover">
            <div class="flex justify-between items-start mb-3 sm:mb-4">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                    <span class="material-symbols-outlined text-xl sm:text-2xl">group</span>
                </div>
                <span class="text-green-700 text-xs sm:text-sm">+12%</span>
            </div>
            <div>
                <h3 class="text-xs sm:text-sm text-gray-500 mb-1">Total Users</h3>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ number_format($totalUsers ?? 5234) }}</p>
            </div>
        </div>

        <div class="bg-red-50 rounded-xl p-4 sm:p-5 border border-red-200 shadow-sm card-hover relative overflow-hidden">
            <div class="flex justify-between items-start mb-3 sm:mb-4">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-red-500 text-white flex items-center justify-center pulse-dot">
                    <span class="material-symbols-outlined fill text-xl sm:text-2xl">emergency_home</span>
                </div>
                <span class="bg-red-500 text-white px-2 py-0.5 rounded text-[10px] sm:text-[11px] font-bold">Critical</span>
            </div>
            <div>
                <h3 class="text-xs sm:text-sm text-red-600 mb-1 font-bold">Active Alerts</h3>
                <p class="text-2xl sm:text-3xl font-bold text-red-600">{{ $activeAlerts ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-200 shadow-sm card-hover">
            <div class="flex justify-between items-start mb-3 sm:mb-4">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-green-50 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl sm:text-2xl">devices</span>
                </div>
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            </div>
            <div>
                <h3 class="text-xs sm:text-sm text-gray-500 mb-1">Tracked Devices</h3>
                <p class="text-2xl sm:text-3xl font-bold text-primary">{{ $trackedDevices ?? 0 }}</p>
            </div>
        </div>

        <!-- Watchtower Widget -->
        @include('admin.dashboard.partials.watchtower')
    </div>

    <!-- Keep the rest of the dashboard content -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Activities</h2>
            <div class="space-y-3">
                <p class="text-gray-500 text-sm">Activity log will appear here</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">System Status</h2>
            <div class="space-y-3">
                <p class="text-gray-500 text-sm">System status will appear here</p>
            </div>
        </div>
    </div>
@endsection
