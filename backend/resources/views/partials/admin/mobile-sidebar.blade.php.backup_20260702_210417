<nav id="mobileSidebar" class="fixed top-0 left-0 h-full w-72 z-50 bg-white border-r border-gray-200 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden overflow-y-auto shadow-xl">
    <div class="pt-20 pb-4 px-5">
        <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-200">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined fill">shield</span>
            </div>
            <div>
                <h2 class="font-bold text-lg text-primary">Admin Console</h2>
                <p class="text-gray-500 text-xs">Safety Management</p>
            </div>
        </div>

        <div class="mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Main</p>
            <ul class="space-y-1">
                <li><a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">dashboard</span>Dashboard</a></li>
                <li><a href="/admin/alerts" class="flex items-center gap-3 px-4 py-3 text-gray-600 rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">emergency_home</span>Kin Alerts</a></li>
                <li><a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.users.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">group</span>Users</a></li>
            </ul>
        </div>

        <!-- OBSERVABILITY SECTION -->

        <!-- SECURITY SECTION -->
        <div class="mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Security</p>
            <ul class="space-y-1">
                <li><a href="{{ route('sentinel.dashboard') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('sentinel.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">security</span>Sentinel</a></li>
            </ul>
        </div>
        <div class="mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Observability</p>
            <ul class="space-y-1">
                <li><a href="{{ route('admin.watchtower.overview') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.watchtower.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">monitoring</span>Watchtower</a></li>
                <li><a href="{{ route('admin.watchtower.incidents') }}" class="flex items-center gap-3 px-4 py-3 pl-8 {{ request()->routeIs('admin.watchtower.incidents') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">warning</span>Incidents</a></li>
                <li><a href="{{ route('admin.watchtower.alert-rules') }}" class="flex items-center gap-3 px-4 py-3 pl-8 {{ request()->routeIs('admin.watchtower.alert-rules') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">notifications</span>Alert Rules</a></li>
            </ul>
        </div>

        <div class="mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Engagement</p>
            <ul class="space-y-1">
                <li><a href="/admin/alerts" class="flex items-center gap-3 px-4 py-3 text-gray-600 rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">campaign</span>Social Campaigns</a></li>
                <li><a href="/admin/alerts" class="flex items-center gap-3 px-4 py-3 text-gray-600 rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">settings_accessibility</span>Community Settings</a></li>
            </ul>
        </div>

        <div class="mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">System</p>
            <ul class="space-y-1">
                <li><a href="/admin/alerts" class="flex items-center gap-3 px-4 py-3 text-gray-600 rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">flag</span>Feature Flags</a></li>
                <li><a href="/admin/alerts" class="flex items-center gap-3 px-4 py-3 text-gray-600 rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">warning</span>Risk Events</a></li>
                <li><a href="{{ route('admin.audit.index') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.audit.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">receipt_long</span>Audit Center</a></li>
                <li><a href="/admin/alerts" class="flex items-center gap-3 px-4 py-3 text-gray-600 rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">admin_panel_settings</span>Admin Accounts</a></li>
                <li><a href="/admin/alerts" class="flex items-center gap-3 px-4 py-3 text-gray-600 rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">settings_input_component</span>System Mode</a></li>
                <li><a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.settings.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50"><span class="material-symbols-outlined">settings</span>General Settings</a></li>
            </ul>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="px-4 py-3 mb-2 bg-gray-50 rounded-lg">
                <div class="text-sm font-medium text-gray-800">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</div>
                <div class="text-xs text-gray-500">{{ Auth::guard('admin')->user()->role ?? 'role' }}</div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-3 text-red-600 rounded-xl hover:bg-red-50 w-full">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</nav>
