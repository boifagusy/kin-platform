<div class="bg-surface-container-lowest rounded-xl border border-gray-200 shadow-premium overflow-hidden">
    @forelse($activities as $activity)
    <div class="p-4 border-b border-gray-100 hover:bg-surface-container-low transition-colors activity-row">
        <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $activity['icon_bg'] ?? 'bg-surface-container-highest' }} {{ $activity['icon_color'] ?? 'text-on-surface-variant' }} flex items-center justify-center">
                <span class="material-symbols-outlined text-lg {{ $activity['icon_fill'] ?? '' }}">{{ $activity['icon'] ?? 'info' }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap justify-between items-baseline gap-2 mb-1">
                    <h4 class="font-body-md font-bold text-on-surface">{{ $activity['title'] }}</h4>
                    <span class="font-label-md text-xs text-on-surface-variant">{{ $activity['time'] }}</span>
                </div>
                <p class="font-body-md text-sm text-on-surface-variant mb-2">{{ $activity['description'] }}</p>
                @if(isset($activity['tags']))
                <div class="flex flex-wrap gap-2">
                    @foreach($activity['tags'] as $tag)
                    <span class="px-2 py-0.5 {{ $tag['class'] ?? 'bg-surface-variant' }} rounded text-xs font-label-md {{ $tag['text_class'] ?? 'text-on-surface-variant' }}">{{ $tag['text'] }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <!-- Default demo activities -->
    <div class="p-4 border-b border-gray-100 hover:bg-surface-container-low transition-colors activity-row">
        <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-danger-light text-danger flex items-center justify-center">
                <span class="material-symbols-outlined text-lg fill">warning</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap justify-between items-baseline gap-2 mb-1">
                    <h4 class="font-body-md font-bold text-on-surface">Emergency Trigger Activated</h4>
                    <span class="font-label-md text-xs text-on-surface-variant">2 mins ago</span>
                </div>
                <p class="font-body-md text-sm text-on-surface-variant mb-2">User ID: #8492 triggered a panic alert in Sector 4.</p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-0.5 bg-surface-variant rounded text-xs font-label-md text-on-surface-variant">High Priority</span>
                    <span class="px-2 py-0.5 bg-danger/10 text-danger rounded text-xs font-label-md font-medium">Requires Response</span>
                </div>
            </div>
        </div>
    </div>
    <div class="p-4 border-b border-gray-100 hover:bg-surface-container-low transition-colors activity-row">
        <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container/20 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">check_circle</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap justify-between items-baseline gap-2 mb-1">
                    <h4 class="font-body-md font-bold text-on-surface">Safe Check-in Completed</h4>
                    <span class="font-label-md text-xs text-on-surface-variant">15 mins ago</span>
                </div>
                <p class="font-body-md text-sm text-on-surface-variant">Group 'Downtown Walkers' finished their scheduled route safely.</p>
            </div>
        </div>
    </div>
    <div class="p-4 border-b border-gray-100 hover:bg-surface-container-low transition-colors activity-row">
        <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">person_add</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap justify-between items-baseline gap-2 mb-1">
                    <h4 class="font-body-md font-bold text-on-surface">New Community Onboarded</h4>
                    <span class="font-label-md text-xs text-on-surface-variant">1 hour ago</span>
                </div>
                <p class="font-body-md text-sm text-on-surface-variant">Local business 'Café Bella' completed business profile setup.</p>
            </div>
        </div>
    </div>
    <div class="p-4 hover:bg-surface-container-low transition-colors activity-row">
        <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">update</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap justify-between items-baseline gap-2 mb-1">
                    <h4 class="font-body-md font-bold text-on-surface">System Maintenance Complete</h4>
                    <span class="font-label-md text-xs text-on-surface-variant">3 hours ago</span>
                </div>
                <p class="font-body-md text-sm text-on-surface-variant">Routine database optimization finished successfully with 0 downtime.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>
