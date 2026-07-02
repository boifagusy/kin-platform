@extends('layouts.admin')

@section('title', 'Alert Rules')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">⚡ Alert Rules</h1>
    <p class="text-gray-500">Configure alert rules and thresholds</p>
</div>

<div class="bg-white rounded-xl p-6 border border-gray-200">
    <div class="text-gray-500 text-sm">
        <p>Alert rules configuration coming soon...</p>
        <p class="mt-2 text-xs">Check back later for alert rule management.</p>
        
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-medium text-gray-700 mb-2">Example Alert Rules</h3>
            <ul class="text-xs text-gray-600 space-y-1">
                <li>• 🔴 Storage > 90% → Critical Alert</li>
                <li>• 🟡 API Error Rate > 5% → Warning Alert</li>
                <li>• 🔵 Queue Backlog > 100 → Info Alert</li>
                <li>• 🟢 Plugin Offline → Critical Alert</li>
            </ul>
        </div>
    </div>
</div>
@endsection
