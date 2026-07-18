{{-- Shared Admin Navigation — Single source of truth for desktop + mobile --}}
{{-- Brick: watchtower_dashboard | OS: v3.2-RC1 --}}

{{-- MAIN SECTION --}}
<p class="text-xs text-gray-400 uppercase tracking-wider px-4 mb-2">Main</p>
<ul class="space-y-1 mb-6">
    <li>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-sm">Dashboard</span>
        </a>
    </li>
    <li>
        <a href="/admin/alerts" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-all">
            <span class="material-symbols-outlined">emergency_home</span>
            <span class="text-sm">Kin Alerts</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.users.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">group</span>
            <span class="text-sm">Users</span>
        </a>
    </li>
</ul>

{{-- ENGAGEMENT SECTION --}}
<p class="text-xs text-gray-400 uppercase tracking-wider px-4 mb-2">Engagement</p>
<ul class="space-y-1 mb-6">
    <li>
        <a href="/admin/alerts" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-all">
            <span class="material-symbols-outlined">campaign</span>
            <span class="text-sm">Social Campaigns</span>
        </a>
    </li>
    <li>
        <a href="/admin/alerts" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-all">
            <span class="material-symbols-outlined">settings_accessibility</span>
            <span class="text-sm">Community Settings</span>
        </a>
    </li>
</ul>

{{-- SYSTEM SECTION --}}
{{-- PLATFORM SECTION --}}
<p class="text-xs text-gray-400 uppercase tracking-wider px-4 mb-2">Platform</p>
<ul class="space-y-1 mb-6">
    <li>
        <a href="{{ route('admin.platform') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.platform') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-sm">Platform Dashboard</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.announcements.index') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.announcements.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">campaign</span>
            <span class="text-sm">Announcements</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.campaigns.index') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.campaigns.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">send</span>
            <span class="text-sm">Campaigns</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.broadcasts.index') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.broadcasts.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">warning</span>
            <span class="text-sm">Emergency Broadcasts</span>
    <li>
        <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.templates.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">description</span>
            <span class="text-sm">Templates</span>
        </a>
    </li>
        </a>
    </li>
</ul>
<p class="text-xs text-gray-400 uppercase tracking-wider px-4 mb-2">System</p>
<ul class="space-y-1 mb-6">
    <li>
        <a href="/admin/alerts" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-all">
            <span class="material-symbols-outlined">flag</span>
            <span class="text-sm">Feature Flags</span>
        </a>
    </li>
    <li>
        <a href="/admin/alerts" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-all">
            <span class="material-symbols-outlined">warning</span>
            <span class="text-sm">Risk Events</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.audit.index') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.audit.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">receipt_long</span>
            <span class="text-sm">Audit Center</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.watchtower') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.watchtower') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">monitoring</span>
            <span class="text-sm">Watchtower</span>
        </a>
    </li>
    <li>
        <a href="/admin/alerts" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-all">
            <span class="material-symbols-outlined">admin_panel_settings</span>
            <span class="text-sm">Admin Accounts</span>
        </a>
    </li>
    <li>
        <a href="/admin/alerts" class="flex items-center gap-3 px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-all">
            <span class="material-symbols-outlined">settings_input_component</span>
            <span class="text-sm">System Mode</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 {{ request()->routeIs('admin.settings.*') ? 'text-primary font-semibold bg-green-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition-all">
            <span class="material-symbols-outlined">settings</span>
            <span class="text-sm">General Settings</span>
        </a>
    </li>
</ul>

{{-- LOGOUT --}}
<div class="mt-6 pt-4 border-t border-gray-200">
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-all w-full">
            <span class="material-symbols-outlined">logout</span>
            <span class="text-sm">Logout</span>
        </button>
    </form>
</div>
