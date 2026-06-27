<div class="bg-surface-container-lowest rounded-xl border border-gray-200 shadow-premium overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-danger fill">warning</span>
            <h3 class="font-semibold text-on-surface">Active Issues Requiring Attention</h3>
        </div>
        <span class="text-xs text-on-surface-variant">{{ count($issues) }} active</span>
    </div>
    
    @forelse($issues as $issue)
    <div class="p-4 border-b border-gray-100 hover:bg-surface-container-low transition-colors">
        <div class="flex gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $issue['icon_bg'] }} {{ $issue['icon_color'] }} flex items-center justify-center">
                <span class="material-symbols-outlined text-lg {{ $issue['priority'] == 'critical' ? 'fill' : '' }}">{{ $issue['icon'] }}</span>
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap justify-between items-baseline gap-2 mb-1">
                    <div class="flex items-center gap-2">
                        <h4 class="font-body-md font-bold text-on-surface">{{ $issue['type_label'] }}</h4>
                        @if($issue['priority'] == 'critical')
                            <span class="px-1.5 py-0.5 bg-danger-light text-danger rounded text-[9px] font-bold uppercase">Critical</span>
                        @elseif($issue['priority'] == 'high')
                            <span class="px-1.5 py-0.5 bg-yellow-50 text-yellow-700 rounded text-[9px] font-bold uppercase">High</span>
                        @endif
                    </div>
                    <span class="font-label-md text-xs text-on-surface-variant">{{ $issue['time'] }}</span>
                </div>
                <p class="font-body-md text-sm text-on-surface-variant mb-2">
                    User #{{ $issue['user_id'] }} - {{ $issue['user_name'] }}
                </p>
                <a href="{{ $issue['action_url'] }}" 
                   class="text-primary hover:underline font-medium text-sm flex items-center gap-1">
                    {{ ucfirst($issue['action']) }} →
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="p-8 text-center">
        <span class="material-symbols-outlined text-4xl text-success fill">check_circle</span>
        <p class="text-on-surface-variant mt-2">No active issues. All systems normal.</p>
    </div>
    @endforelse
</div>
