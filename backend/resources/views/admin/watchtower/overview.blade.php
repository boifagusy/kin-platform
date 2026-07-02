@extends('layouts.admin')

@section('title', 'Operations Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">⚙️ Operations Dashboard</h1>
    <p class="text-gray-500">Live system health and incident management</p>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-green-500" id="incident-total">--</div>
        <div class="text-sm text-gray-500">Total Incidents</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-red-500" id="incident-critical">--</div>
        <div class="text-sm text-gray-500">Critical</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-yellow-500" id="incident-open">--</div>
        <div class="text-sm text-gray-500">Open Incidents</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-blue-500" id="incident-resolved">--</div>
        <div class="text-sm text-gray-500">Resolved (24h)</div>
    </div>
</div>

<!-- Incident List -->
<div class="bg-white rounded-xl p-6 border border-gray-200">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Active Incidents</h2>
    <div id="incident-list" class="space-y-2">
        <div class="text-gray-500 text-sm">Loading incidents...</div>
    </div>
</div>

<script>
fetch('/api/watchtower/incidents')
    .then(r => r.json())
    .then(data => {
        document.getElementById('incident-total').textContent = data.total || 0;
        document.getElementById('incident-critical').textContent = data.critical || 0;
        document.getElementById('incident-open').textContent = data.open || 0;
        document.getElementById('incident-resolved').textContent = data.resolved || 0;
        
        const list = document.getElementById('incident-list');
        if (data.incidents && data.incidents.length > 0) {
            list.innerHTML = data.incidents.map(i => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border-l-4 border-${i.severity === 'critical' ? 'red' : 'yellow'}-500">
                    <div>
                        <div class="font-medium text-gray-800">${i.title}</div>
                        <div class="text-xs text-gray-500">${i.source || 'Unknown'} • ${new Date(i.detected_at || Date.now()).toLocaleString()}</div>
                    </div>
                    <span class="px-2 py-1 text-xs rounded ${i.status === 'new' ? 'bg-red-100 text-red-700' : i.status === 'investigating' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'}">
                        ${i.status || 'new'}
                    </span>
                </div>
            `).join('');
        } else {
            list.innerHTML = '<div class="text-gray-500 text-sm">✅ No active incidents</div>';
        }
    })
    .catch(() => {
        document.getElementById('incident-list').innerHTML = '<div class="text-red-500 text-sm">Failed to load incidents</div>';
    });
</script>
@endsection
