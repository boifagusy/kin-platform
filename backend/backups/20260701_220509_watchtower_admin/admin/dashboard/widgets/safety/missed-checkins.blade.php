<div class="bg-white rounded-xl p-4 border-l-4 border-warning shadow-sm card-hover">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">schedule</span>
            </div>
            <span class="text-xs text-gray-500 font-medium">Missed Today</span>
        </div>
        @if($data['trend'] == 'up')
            <span class="text-xs text-danger flex items-center gap-0.5">↑ {{ abs($data['change']) }}</span>
        @elseif($data['trend'] == 'down')
            <span class="text-xs text-success flex items-center gap-0.5">↓ {{ abs($data['change']) }}</span>
        @else
            <span class="text-xs text-gray-400">→</span>
        @endif
    </div>
    <div class="text-2xl font-bold text-yellow-700">{{ $data['count'] }}</div>
    <div class="text-xs text-gray-400 mt-1">Missed check-ins</div>
</div>
