@extends('layouts.admin')

@section('title', 'Compliance Report')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">📋 Compliance Report</h1>
    <p class="text-gray-500">Security compliance and audit reports</p>
</div>

<!-- Report Header -->
<div class="bg-white rounded-xl p-4 border border-gray-200 mb-6">
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Security Compliance Report</h2>
            <p class="text-sm text-gray-500">Generated: {{ $report['generated_at'] }}</p>
        </div>
        <div class="flex gap-2">
            <a href="?export=true&format=json" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm">
                Export JSON
            </a>
            <a href="?export=true&format=csv" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm">
                Export CSV
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-blue-500">{{ $report['summary']['total_events'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Total Events</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-red-500">{{ $report['summary']['critical_events'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Critical Events</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-green-500">{{ $report['summary']['resolution_rate'] ?? 0 }}%</div>
        <div class="text-sm text-gray-500">Resolution Rate</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-yellow-500">{{ $report['summary']['warning_events'] ?? 0 }}</div>
        <div class="text-sm text-gray-500">Warnings</div>
    </div>
</div>

<!-- Findings -->
<div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
    <h3 class="font-semibold text-gray-700 mb-4">🔍 Findings</h3>
    <div class="space-y-3">
        @foreach($report['findings'] ?? [] as $finding)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <span class="font-medium text-gray-700">{{ $finding['category'] }}</span>
                    <span class="text-sm text-gray-500 ml-3">{{ $finding['details'] }}</span>
                </div>
                <span class="px-3 py-1 text-xs rounded {{ $finding['status'] === 'Pass' ? 'bg-green-100 text-green-700' : ($finding['status'] === 'Warning' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ $finding['status'] }}
                </span>
            </div>
        @endforeach
    </div>
</div>

<!-- Recommendations -->
<div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
    <h3 class="font-semibold text-blue-700 mb-3">💡 Recommendations</h3>
    <ul class="space-y-2">
        @foreach($report['recommendations'] ?? [] as $recommendation)
            <li class="flex items-start gap-2 text-sm text-blue-600">
                <span>•</span>
                <span>{{ $recommendation }}</span>
            </li>
        @endforeach
    </ul>
</div>
@endsection
