@extends('layouts.admin')

@section('title', 'Security Operations Center')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">🛡️ Security Operations Center</h1>
    <p class="text-gray-500">Real-time security monitoring and threat intelligence</p>
</div>

<!-- Executive Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-green-500" id="security-score">--</div>
        <div class="text-sm text-gray-500">Security Score</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-red-500" id="active-threats">--</div>
        <div class="text-sm text-gray-500">Active Threats</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-yellow-500" id="high-risk-users">--</div>
        <div class="text-sm text-gray-500">High Risk Users</div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="text-2xl font-bold text-orange-500" id="locked-accounts">--</div>
        <div class="text-sm text-gray-500">Locked Accounts</div>
    </div>
</div>

<!-- Live Threat Feed -->
<div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">🚨 Live Threat Feed</h2>
        <button onclick="refreshThreats()" class="text-sm text-blue-500 hover:text-blue-700">Refresh</button>
    </div>
    <div id="threat-feed" class="space-y-2">
        <div class="text-gray-500 text-sm">Loading threats...</div>
    </div>
</div>

<!-- High Risk Users -->
<div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">⚠️ High Risk Users</h2>
    <div id="high-risk-users-list" class="overflow-x-auto">
        <div class="text-gray-500 text-sm">Loading...</div>
    </div>
</div>

<!-- Security Timeline -->
<div class="bg-white rounded-xl p-6 border border-gray-200">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">📋 Security Timeline</h2>
    <div id="security-timeline" class="space-y-2">
        <div class="text-gray-500 text-sm">Loading...</div>
    </div>
</div>

<script>
// Fetch metrics
function fetchMetrics() {
    fetch('/admin/sentinel/metrics')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('security-score').textContent = data.data.security_score || 0;
                document.getElementById('active-threats').textContent = data.data.active_threats || 0;
                document.getElementById('high-risk-users').textContent = data.data.high_risk_users || 0;
                document.getElementById('locked-accounts').textContent = data.data.locked_accounts || 0;
            }
        })
        .catch(() => console.error('Failed to fetch metrics'));
}

// Fetch threats
function fetchThreats() {
    fetch('/admin/sentinel/threats')
        .then(r => r.json())
        .then(data => {
            const feed = document.getElementById('threat-feed');
            if (data.success && data.data.length > 0) {
                feed.innerHTML = data.data.slice(0, 10).map(t => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border-l-4 border-${t.severity === 'critical' ? 'red' : t.severity === 'high' ? 'orange' : 'yellow'}-500">
                        <div>
                            <div class="font-medium text-gray-800">${t.threat_type}</div>
                            <div class="text-xs text-gray-500">${t.time ? new Date(t.time).toLocaleString() : ''} • ${t.ip}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 text-xs rounded ${t.severity === 'critical' ? 'bg-red-100 text-red-700' : t.severity === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700'}">
                                ${t.severity}
                            </span>
                            <span class="px-2 py-1 text-xs rounded ${t.status === 'Resolved' ? 'bg-green-100 text-green-700' : t.status === 'Mitigated' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700'}">
                                ${t.status}
                            </span>
                        </div>
                    </div>
                `).join('');
            } else {
                feed.innerHTML = '<div class="text-gray-500 text-sm">✅ No active threats</div>';
            }
        })
        .catch(() => console.error('Failed to fetch threats'));
}

// Fetch high risk users
function fetchHighRiskUsers() {
    fetch('/admin/sentinel/high-risk-users')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('high-risk-users-list');
            if (data.success && data.data.length > 0) {
                list.innerHTML = `
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-gray-200">
                                <th class="pb-2">User</th>
                                <th class="pb-2">Risk Score</th>
                                <th class="pb-2">Failed Logins</th>
                                <th class="pb-2">Recommended Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.data.map(u => `
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2">${u.name}</td>
                                    <td class="py-2 font-medium" style="color: ${u.risk_score > 70 ? '#D32F2F' : u.risk_score > 40 ? '#FF6F00' : '#00C853'}">${u.risk_score}</td>
                                    <td class="py-2">${u.failed_logins}</td>
                                    <td class="py-2 text-xs">${u.recommended_action}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                list.innerHTML = '<div class="text-gray-500 text-sm">✅ No high risk users</div>';
            }
        })
        .catch(() => console.error('Failed to fetch high risk users'));
}

// Fetch timeline
function fetchTimeline() {
    fetch('/admin/sentinel/timeline')
        .then(r => r.json())
        .then(data => {
            const timeline = document.getElementById('security-timeline');
            if (data.success && data.data.length > 0) {
                timeline.innerHTML = data.data.slice(0, 20).map(t => `
                    <div class="flex items-start gap-3 p-2 border-b border-gray-100">
                        <div class="w-2 h-2 rounded-full mt-2 ${t.severity === 'critical' ? 'bg-red-500' : t.severity === 'high' ? 'bg-orange-500' : 'bg-blue-500'}"></div>
                        <div>
                            <div class="text-sm font-medium text-gray-800">${t.event_type}</div>
                            <div class="text-xs text-gray-500">${t.timestamp ? new Date(t.timestamp).toLocaleString() : ''} • ${t.user || 'Unknown'}</div>
                        </div>
                        <div class="ml-auto text-xs text-gray-400">${t.action}</div>
                    </div>
                `).join('');
            } else {
                timeline.innerHTML = '<div class="text-gray-500 text-sm">No security events</div>';
            }
        })
        .catch(() => console.error('Failed to fetch timeline'));
}

// Refresh all data
function refreshAll() {
    fetchMetrics();
    fetchThreats();
    fetchHighRiskUsers();
    fetchTimeline();
}

// Auto-refresh every 30 seconds
setInterval(refreshAll, 30000);

// Initial load
refreshAll();
</script>
@endsection
