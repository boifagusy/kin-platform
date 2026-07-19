@extends('layouts.admin')

@section('title', 'Version Analytics')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Version Analytics</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <p class="text-xs text-gray-500 uppercase">Total Versions</p>
            <p class="text-2xl font-bold text-[#1A5632]">{{ $analytics['summary']['total_versions'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <p class="text-xs text-gray-500 uppercase">Active</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $analytics['summary']['active_versions'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <p class="text-xs text-gray-500 uppercase">Scheduled</p>
            <p class="text-2xl font-bold text-amber-600">{{ $analytics['summary']['scheduled_releases'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <p class="text-xs text-gray-500 uppercase">Expired</p>
            <p class="text-2xl font-bold text-red-600">{{ $analytics['summary']['expired_releases'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <p class="text-xs text-gray-500 uppercase">Deleted</p>
            <p class="text-2xl font-bold text-gray-400">{{ $analytics['summary']['soft_deleted'] }}</p>
        </div>
    </div>

    <!-- Distribution Chart -->
    <div class="bg-white rounded-xl p-6 shadow-sm border mb-8">
        <h2 class="font-semibold text-gray-700 mb-4">Version Distribution</h2>
        <canvas id="distributionChart" height="80"></canvas>
    </div>

    <!-- Scheduled & Expired Tables -->
    <div class="grid grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-sm border">
            <h2 class="font-semibold text-gray-700 mb-4">Scheduled Releases</h2>
            @if(empty($analytics['scheduled']))
                <p class="text-sm text-gray-400">No scheduled releases.</p>
            @else
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500"><th>Version</th><th>Platform</th><th>Starts</th><th>Policy</th></tr></thead>
                    <tbody>
                        @foreach($analytics['scheduled'] as $s)
                            <tr class="border-t"><td class="py-2">{{ $s['version_name'] }}</td><td>{{ $s['platform'] }}</td><td>{{ \Carbon\Carbon::parse($s['starts_at'])->format('M d, Y H:i') }}</td><td>{{ $s['policy'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border">
            <h2 class="font-semibold text-gray-700 mb-4">Expired Releases</h2>
            @if(empty($analytics['expired']))
                <p class="text-sm text-gray-400">No expired releases.</p>
            @else
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500"><th>Version</th><th>Platform</th><th>Expired</th><th>Policy</th></tr></thead>
                    <tbody>
                        @foreach($analytics['expired'] as $e)
                            <tr class="border-t"><td class="py-2">{{ $e['version_name'] }}</td><td>{{ $e['platform'] }}</td><td>{{ \Carbon\Carbon::parse($e['expired_at'])->format('M d, Y H:i') }}</td><td>{{ $e['policy'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white rounded-xl p-6 shadow-sm border">
        <h2 class="font-semibold text-gray-700 mb-4">Release Timeline</h2>
        @if(empty($analytics['timeline']))
            <p class="text-sm text-gray-400">No release activity recorded yet.</p>
        @else
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500"><th>Timestamp</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($analytics['timeline'] as $t)
                        <tr class="border-t"><td class="py-2 text-gray-500">{{ $t['timestamp'] }}</td><td>{{ $t['action'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
new Chart(document.getElementById('distributionChart'), {
    type: 'bar',
    data: {
        labels: ['Active', 'Inactive', 'Deleted'],
        datasets: [{
            label: 'Versions',
            data: [
                {{ $analytics['distribution']['active'] }},
                {{ $analytics['distribution']['inactive'] }},
                {{ $analytics['distribution']['deleted'] }}
            ],
            backgroundColor: ['#059669', '#d1d5db', '#f87171'],
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
@endsection
