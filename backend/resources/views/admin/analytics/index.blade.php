@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Platform Analytics</h1>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-4 shadow-sm border">
        <p class="text-xs text-gray-500 uppercase">Announcements</p>
        <p class="text-2xl font-bold text-[#1A5632]">{{ $stats['announcements']['total'] }}</p>
        <p class="text-xs text-gray-400">{{ $stats['announcements']['published'] }} published</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border">
        <p class="text-xs text-gray-500 uppercase">Campaigns</p>
        <p class="text-2xl font-bold text-[#1A5632]">{{ $stats['campaigns']['total_deliveries'] }}</p>
        <p class="text-xs text-gray-400">{{ $stats['campaigns']['sent'] }} sent</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border">
        <p class="text-xs text-gray-500 uppercase">Notifications</p>
        <p class="text-2xl font-bold text-[#1A5632]">{{ $stats['notifications']['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border">
        <p class="text-xs text-gray-500 uppercase">Versions</p>
        <p class="text-2xl font-bold text-[#1A5632]">{{ $stats['versions']['total'] }}</p>
        <p class="text-xs text-gray-400">{{ $stats['versions']['active'] }} active</p>
    </div>
</div>
@endsection
