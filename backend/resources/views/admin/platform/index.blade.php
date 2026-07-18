@extends('layouts.admin')
@section('title', 'Platform Overview')
@section('content')

@include('admin.platform.partials.header', [
    'title' => 'Platform Overview',
    'description' => 'Announcement Platform — real-time metrics and management.',
    'actions' => [
        ['label' => '+ Announcement', 'route' => route('admin.announcements.create'), 'icon' => 'add', 'class' => 'bg-[#1A5632] text-white'],
        ['label' => '+ Campaign', 'route' => route('admin.campaigns.create'), 'icon' => 'send', 'class' => 'bg-blue-500 text-white'],
        ['label' => '+ Broadcast', 'route' => route('admin.broadcasts.create'), 'icon' => 'warning', 'class' => 'bg-red-500 text-white'],
    ]
])

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
        <h3 class="font-semibold text-gray-800 mb-3">Quick Links</h3>
        <div class="space-y-2">
            <a href="{{ route('admin.announcements.index') }}" class="block px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm text-gray-700">📢 Manage Announcements →</a>
            <a href="{{ route('admin.campaigns.index') }}" class="block px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm text-gray-700">📤 Manage Campaigns →</a>
            <a href="{{ route('admin.broadcasts.index') }}" class="block px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm text-gray-700">⚠️ Manage Broadcasts →</a>
        </div>
    </div>
</div>
@endsection
