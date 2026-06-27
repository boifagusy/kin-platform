<div class="bg-white rounded-xl p-4 border-l-4 border-conversion shadow-sm card-hover">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-conversion/20 text-conversion flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">lock</span>
            </div>
            <span class="text-xs text-gray-500 font-medium">Duress Today</span>
        </div>
        @if($data['trend'] == 'up')
            <span class="text-xs text-danger flex items-center gap-0.5">↑ {{ abs($data['change']) }}</span>
        @elseif($data['trend'] == 'down')
            <span class="text-xs text-success flex items-center gap-0.5">↓ {{ abs($data['change']) }}</span>
        @else
            <span class="text-xs text-gray-400">→</span>
        @endif
    </div>
    <div class="text-2xl font-bold text-conversion">{{ $data['count'] }}</div>
    <div class="text-xs text-gray-400 mt-1">Duress PIN used</div>
</div>
