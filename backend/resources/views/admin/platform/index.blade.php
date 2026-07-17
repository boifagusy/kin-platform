@extends('layouts.admin')

@section('title', 'Platform Overview')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Platform Overview</h1>
    <p class="text-sm text-gray-500 mt-1">Announcement Platform — real-time metrics</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <div class="flex items-center gap-3 mb-3">
            <span class="material-symbols-outlined text-blue-500">campaign</span>
            <p class="text-xs text-gray-500 uppercase font-medium">Announcements</p>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['announcements']['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['announcements']['published'] }} published</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <div class="flex items-center gap-3 mb-3">
            <span class="material-symbols-outlined text-green-500">send</span>
            <p class="text-xs text-gray-500 uppercase font-medium">Campaigns</p>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['campaigns']['total_deliveries'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['campaigns']['sent'] }} sent</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <div class="flex items-center gap-3 mb-3">
            <span class="material-symbols-outlined text-purple-500">notifications</span>
            <p class="text-xs text-gray-500 uppercase font-medium">Notifications</p>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['notifications']['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1">incident alerts</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <div class="flex items-center gap-3 mb-3">
            <span class="material-symbols-outlined text-orange-500">update</span>
            <p class="text-xs text-gray-500 uppercase font-medium">Versions</p>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['versions']['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['versions']['active'] }} active</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <h3 class="font-semibold text-gray-800 mb-3">Emergency Broadcasts</h3>
        <div class="flex items-baseline gap-2">
            <p class="text-3xl font-bold text-gray-800">{{ $stats['emergency_broadcasts']['total'] }}</p>
            <p class="text-sm text-gray-500">total</p>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['emergency_broadcasts']['active'] }} active</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <h3 class="font-semibold text-gray-800 mb-3">Quick Actions</h3>
        <div class="flex gap-2">
            <a href="/admin/announcements/create" class="px-4 py-2 bg-[#1A5632] text-white text-sm rounded-lg">+ Announcement</a>
            <a href="/admin/push-campaigns/create" class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg">+ Campaign</a>
            <a href="/admin/emergency-broadcasts/create" class="px-4 py-2 bg-red-500 text-white text-sm rounded-lg">+ Broadcast</a>
        </div>
    </div>
</div>
@endsection
