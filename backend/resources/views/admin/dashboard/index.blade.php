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
        
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-gray-200 shadow-sm card-hover border-b-4 border-b-yellow-500">
            <div class="flex justify-between items-start mb-3 sm:mb-4">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl sm:text-2xl">storefront</span>
                </div>
            </div>
            <div>
                <h3 class="text-xs sm:text-sm text-gray-500 mb-1">Business Accounts</h3>
                <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ $businessAccounts ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-red-500 fill text-2xl">emergency_home</span>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Safety Monitor</h2>
            <span class="px-2 py-0.5 bg-red-100 text-red-600 rounded text-[10px] font-bold animate-pulse">LIVE</span>
        </div>
        
        <div class="bg-white rounded-xl border p-6" id="safetyMonitorContent">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-green-500 border-t-transparent"></div>
                <p class="text-gray-500 mt-2">Loading safety data...</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
        <div class="lg:col-span-1">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">Apps Overview</h2>
            <div class="bg-white rounded-xl p-5 border border-green-200 shadow-sm card-hover mb-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-primary text-white flex items-center justify-center">
                            <span class="material-symbols-outlined fill text-xl">shield</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Kin</h3>
                            <p class="text-xs text-gray-500">Production</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-green-100 text-primary rounded-full text-[10px] font-bold">Active</span>
                </div>
                <div class="flex justify-between text-gray-500 text-sm pt-4 border-t border-gray-100">
                    <span>Version 2.4.1</span>
                    <a href="#" class="text-primary hover:underline font-bold">Manage →</a>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm opacity-80 hover:opacity-100 transition-all cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center border border-dashed">
                        <span class="material-symbols-outlined">science</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Project Nova</h3>
                        <p class="text-xs text-gray-500">Staging</p>
                    </div>
                    <span class="ml-auto text-xs text-gray-400">In Dev</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Recent Activity</h2>
                <button class="text-sm text-primary hover:bg-green-50 px-3 py-2 rounded-lg">View All →</button>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                @forelse($recentAlerts ?? [] as $alert)
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg fill">warning</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-baseline mb-1">
                                <h4 class="font-bold text-gray-800">Emergency Alert</h4>
                                <span class="text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-500">User #{{ $alert->user_id }} - Priority: {{ $alert->priority ?? 'high' }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500">No recent alerts</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function loadSafetyMonitor() {
    fetch('{{ route("admin.safety.metrics") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const html = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-xl p-4 border-l-4 border-red-500 shadow-sm">
                            <div class="text-2xl font-bold text-red-500">${data.data.active_sos.count}</div>
                            <div class="text-sm text-gray-500">Active SOS</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 border-l-4 border-yellow-500 shadow-sm">
                            <div class="text-2xl font-bold text-yellow-600">${data.data.missed_today.count}</div>
                            <div class="text-sm text-gray-500">Missed Today</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 border-l-4 border-green-500 shadow-sm">
                            <div class="text-2xl font-bold text-green-600">${data.data.duress_today.count}</div>
                            <div class="text-sm text-gray-500">Duress Today</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 border-l-4 border-blue-500 shadow-sm">
                            <div class="text-2xl font-bold text-blue-600">${data.data.pending_escalations.count}</div>
                            <div class="text-sm text-gray-500">Pending Escalations</div>
                        </div>
                    </div>
                    <div class="text-right text-xs text-gray-400">Updated: ${new Date().toLocaleTimeString()}</div>
                `;
                document.getElementById('safetyMonitorContent').innerHTML = html;
            }
        })
        .catch(error => {
            document.getElementById('safetyMonitorContent').innerHTML = '<div class="text-center text-red-500">Failed to load safety data</div>';
        });
}
document.addEventListener('DOMContentLoaded', loadSafetyMonitor);
setInterval(loadSafetyMonitor, 30000);
</script>
@endpush
