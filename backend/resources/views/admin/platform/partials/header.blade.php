{{-- Shared Platform Header --}}
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.platform') }}" class="hover:text-primary transition">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('admin.platform') }}" class="hover:text-primary transition">Platform</a>
        @if(isset($breadcrumb))
        <span class="text-gray-300">/</span>
        <span class="text-gray-700 font-medium">{{ $breadcrumb }}</span>
        @endif
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $title ?? 'Platform' }}</h1>
            @if(isset($description))
            <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
            @endif
        </div>

        @if(isset($actions))
        <div class="flex flex-wrap gap-2">
            @foreach($actions as $action)
                @if(isset($action['route']))
                <a href="{{ $action['route'] }}" class="px-4 py-2 {{ $action['class'] ?? 'bg-[#1A5632] text-white' }} rounded-lg text-sm font-medium hover:opacity-90 transition whitespace-nowrap">
                    @if(isset($action['icon']))
                    <span class="material-symbols-outlined text-sm align-middle mr-1">{{ $action['icon'] }}</span>
                    @endif
                    {{ $action['label'] }}
                </a>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    <div class="flex gap-1 mt-4 overflow-x-auto pb-1">
        @php
        $tabs = [
            ['route' => 'admin.platform', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['route' => 'admin.announcements.index', 'label' => 'Announcements', 'icon' => 'campaign'],
            ['route' => 'admin.campaigns.index', 'label' => 'Campaigns', 'icon' => 'send'],
            ['route' => 'admin.broadcasts.index', 'label' => 'Broadcasts', 'icon' => 'warning'],
            ['route' => 'admin.templates.index', 'label' => 'Templates', 'icon' => 'description'],
        ];
        @endphp
        @foreach($tabs as $tab)
        <a href="{{ route($tab['route']) }}" 
           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium whitespace-nowrap transition
           {{ request()->routeIs($tab['route']) || request()->routeIs($tab['route'].'.*') ? 'bg-[#1A5632] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <span class="material-symbols-outlined text-sm">{{ $tab['icon'] }}</span>
            {{ $tab['label'] }}
        </a>
        @endforeach
    </div>
</div>
