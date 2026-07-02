<!-- Watchtower System Health Widget -->
<div 
    id="watchtower-widget"
    class="bg-white rounded-xl p-4 sm:p-5 border border-gray-200 shadow-sm card-hover cursor-pointer hover:shadow-md transition-shadow"
    onclick="openWatchtowerDetails()"
>
    <div class="flex justify-between items-start mb-3 sm:mb-4">
        <div>
            <h3 class="text-xs sm:text-sm text-gray-500 mb-1">🔍 System Health</h3>
            <div id="watchtower-health-score" class="text-2xl sm:text-3xl font-bold text-primary">Loading...</div>
        </div>
        <div id="watchtower-health-status" class="w-2 h-2 rounded-full bg-gray-300 animate-pulse"></div>
    </div>
    <div id="watchtower-details" class="mt-2">
        <div class="flex justify-between text-xs text-gray-500">
            <span id="watchtower-storage">Storage: --</span>
            <span id="watchtower-plugins">Plugins: --</span>
            <span id="watchtower-uptime">Uptime: --</span>
        </div>
    </div>
    <div id="watchtower-error" class="hidden text-red-500 text-xs mt-2"></div>
    <div class="mt-3 text-xs text-blue-500 hover:text-blue-700 flex items-center gap-1">
        <span>Click to view details</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </div>
</div>

<!-- Modal for Watchtower Details -->
<div id="watchtower-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">🔍 Watchtower System Health</h2>
            <button onclick="closeWatchtowerModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <div id="watchtower-modal-content" class="space-y-4">
                <div class="text-center text-gray-500">Loading detailed health data...</div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end p-6 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closeWatchtowerModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Close
            </button>
            <button onclick="refreshWatchtowerData()" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Refresh
            </button>
        </div>
    </div>
</div>

<script>
// Store the last fetched data
let lastWatchtowerData = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Watchtower widget loading...');
    fetchWatchtowerData();
});

function fetchWatchtowerData() {
    fetch('/api/watchtower/dashboard')
        .then(response => response.json())
        .then(data => {
            console.log('✅ Watchtower data received:', data);
            lastWatchtowerData = data;
            updateWatchtowerWidget(data);
        })
        .catch(error => {
            console.error('❌ Watchtower error:', error);
            document.getElementById('watchtower-health-score').textContent = '⚠️';
            document.getElementById('watchtower-health-score').style.color = '#EF4444';
            document.getElementById('watchtower-error').textContent = 'Unable to fetch system status';
            document.getElementById('watchtower-error').className = 'text-red-500 text-xs mt-2';
        });
}

function updateWatchtowerWidget(data) {
    const status = data.data || data;
    const health = status.health || { score: 0, status: 'unknown' };
    const storage = status.storage || { used_percent: 0, free_gb: 0 };
    const plugins = status.plugins || {};
    const pluginCount = Array.isArray(plugins) ? plugins.length : Object.keys(plugins).length;
    
    // Update health score
    const scoreEl = document.getElementById('watchtower-health-score');
    const statusEl = document.getElementById('watchtower-health-status');
    
    if (health.score !== undefined && health.score !== null) {
        scoreEl.textContent = health.score + '%';
        scoreEl.style.color = health.color || '#4CAF50';
    } else {
        scoreEl.textContent = '--';
    }
    
    // Update status indicator
    const statusClass = health.status === 'healthy' || health.status === 'excellent' ? 'bg-green-500' : 
                        health.status === 'warning' || health.status === 'degraded' ? 'bg-yellow-500' :
                        health.status === 'critical' ? 'bg-red-500' : 'bg-gray-300';
    statusEl.className = 'w-2 h-2 rounded-full ' + statusClass + ' animate-pulse';
    
    // Update details
    document.getElementById('watchtower-storage').textContent = 'Storage: ' + (storage.used_percent || 0) + '% used';
    document.getElementById('watchtower-plugins').textContent = 'Plugins: ' + pluginCount;
    document.getElementById('watchtower-uptime').textContent = 'Updated: ' + new Date().toLocaleTimeString();
}

function openWatchtowerDetails() {
    console.log('🔍 Opening Watchtower details...');
    const modal = document.getElementById('watchtower-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Show loading
    document.getElementById('watchtower-modal-content').innerHTML = 
        '<div class="text-center py-8 text-gray-500">Loading detailed health data...</div>';
    
    // If we have data, show it
    if (lastWatchtowerData) {
        renderWatchtowerDetails(lastWatchtowerData);
    } else {
        fetch('/api/watchtower/dashboard')
            .then(response => response.json())
            .then(data => {
                lastWatchtowerData = data;
                renderWatchtowerDetails(data);
            })
            .catch(error => {
                document.getElementById('watchtower-modal-content').innerHTML = 
                    '<div class="text-center py-8 text-red-500">❌ Failed to load details. Please refresh.</div>';
            });
    }
}

function renderWatchtowerDetails(data) {
    const status = data.data || data;
    const health = status.health || { score: 0, level: 'Unknown', color: '#4CAF50' };
    const storage = status.storage || { used_percent: 0, free_gb: 0, total_gb: 0 };
    const plugins = status.plugins || {};
    const api = status.api || {};
    const queue = status.queue || {};
    const safety = status.safety || {};
    
    const pluginCount = Array.isArray(plugins) ? plugins.length : Object.keys(plugins).length;
    const pluginList = Array.isArray(plugins) ? plugins : Object.values(plugins);
    
    let html = `
        <!-- Health Score -->
        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-center">
            <div class="text-5xl font-bold" style="color: ${health.color}">
                ${health.score || 0}%
            </div>
            <div class="text-sm text-gray-500 mt-1">Overall System Health</div>
            <div class="text-sm font-medium mt-2" style="color: ${health.color}">
                ${health.level || 'Unknown'}
            </div>
            <div class="text-xs text-gray-400 mt-1">${health.message || ''}</div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold text-gray-800 dark:text-white">${api.requests_per_minute || 0}</div>
                <div class="text-xs text-gray-500">API Requests/min</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold text-gray-800 dark:text-white">${queue.pending || 0}</div>
                <div class="text-xs text-gray-500">Queue Pending</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold text-gray-800 dark:text-white">${storage.free_gb || 0} GB</div>
                <div class="text-xs text-gray-500">Free Storage</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold text-gray-800 dark:text-white">${pluginCount}</div>
                <div class="text-xs text-gray-500">Active Plugins</div>
            </div>
        </div>

        <!-- Storage Details -->
        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">💾 Storage</h4>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Used: ${storage.used_percent || 0}%</span>
                <span class="text-gray-500">Free: ${storage.free_gb || 0} GB / ${storage.total_gb || 0} GB</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                <div class="h-2 rounded-full ${storage.used_percent > 90 ? 'bg-red-500' : storage.used_percent > 75 ? 'bg-yellow-500' : 'bg-green-500'}" 
                     style="width: ${Math.min(storage.used_percent || 0, 100)}%"></div>
            </div>
            ${storage.used_percent > 90 ? '<div class="text-xs text-red-500 mt-1">⚠️ Storage is critically low!</div>' : ''}
        </div>

        <!-- Safety Metrics -->
        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">🛡️ Safety Engine</h4>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div>
                    <div class="text-xl font-bold text-gray-800 dark:text-white">${safety.checkins_today || 0}</div>
                    <div class="text-xs text-gray-500">Check-ins Today</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-800 dark:text-white">${safety.active_sos || 0}</div>
                    <div class="text-xs text-gray-500">Active SOS</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-800 dark:text-white">${safety.confidence_score || 0}%</div>
                    <div class="text-xs text-gray-500">Confidence Score</div>
                </div>
            </div>
        </div>
    `;

    // Plugins List
    if (pluginList.length > 0) {
        html += `
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
                <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">🔌 Plugins (${pluginCount})</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    ${pluginList.map(p => `
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-2 h-2 rounded-full ${p.status === 'healthy' || p.status === 'healthy' ? 'bg-green-500' : 'bg-red-500'}"></span>
                            <span class="text-gray-700 dark:text-gray-300">${p.name || p.class || 'Unknown'}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Performance
    const performance = status.performance || {};
    html += `
        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">⚡ Performance</h4>
            <div class="grid grid-cols-2 gap-2 text-center">
                <div>
                    <div class="text-xl font-bold text-gray-800 dark:text-white">${performance.memory?.used_percentage || 0}%</div>
                    <div class="text-xs text-gray-500">Memory Usage</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-800 dark:text-white">${api.p95_latency || 0}ms</div>
                    <div class="text-xs text-gray-500">P95 Response Time</div>
                </div>
            </div>
        </div>
    `;

    html += `
        <div class="text-xs text-gray-400 text-center mt-2">
            Last updated: ${new Date(status.timestamp || Date.now()).toLocaleString()}
        </div>
    `;

    document.getElementById('watchtower-modal-content').innerHTML = html;
}

function closeWatchtowerModal() {
    const modal = document.getElementById('watchtower-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function refreshWatchtowerData() {
    document.getElementById('watchtower-modal-content').innerHTML = 
        '<div class="text-center py-8 text-gray-500">Refreshing data...</div>';
    fetchWatchtowerData();
    setTimeout(() => {
        if (lastWatchtowerData) {
            renderWatchtowerDetails(lastWatchtowerData);
        }
    }, 500);
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeWatchtowerModal();
    }
});

// Close modal when clicking outside
document.getElementById('watchtower-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeWatchtowerModal();
    }
});
</script>

<style>
    .card-hover {
        transition: all 0.2s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
    }
    #watchtower-modal {
        backdrop-filter: blur(4px);
    }
</style>
