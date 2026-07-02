<div class="bg-surface-container-lowest rounded-xl p-5 border border-gray-200 shadow-premium">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h3 class="font-semibold text-on-surface">Safety Score Trend</h3>
            <p class="text-xs text-on-surface-variant">Last 7 days</p>
        </div>
        <div class="text-right">
            <div class="text-2xl font-bold text-primary">{{ $data['current'] }}%</div>
            @if($data['trend'] == 'up')
                <span class="text-xs text-success flex items-center gap-0.5">↑ +{{ $data['change'] }}%</span>
            @elseif($data['trend'] == 'down')
                <span class="text-xs text-danger flex items-center gap-0.5">↓ {{ $data['change'] }}%</span>
            @else
                <span class="text-xs text-gray-400">→ No change</span>
            @endif
        </div>
    </div>
    
    <div class="relative h-32 mb-2">
        <canvas id="safetyScoreChart" class="w-full h-full"></canvas>
    </div>
    
    <div class="flex justify-between text-xs text-on-surface-variant mt-2">
        @foreach($data['data'] as $point)
            <span>{{ $point['day'] }}</span>
        @endforeach
    </div>
    
    <div class="mt-4 pt-3 border-t border-gray-100">
        <div class="flex justify-between text-sm">
            <span class="text-on-surface-variant">Overall Status</span>
            @if($data['current'] >= 90)
                <span class="text-success font-semibold">Excellent</span>
            @elseif($data['current'] >= 75)
                <span class="text-primary font-semibold">Good</span>
            @elseif($data['current'] >= 60)
                <span class="text-warning font-semibold">Fair</span>
            @else
                <span class="text-danger font-semibold">Needs Attention</span>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('safetyScoreChart');
    if (ctx) {
        const scores = @json(array_column($data['data'], 'score'));
        const days = @json(array_column($data['data'], 'day'));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: days,
                datasets: [{
                    label: 'Safety Score',
                    data: scores,
                    borderColor: '#1a5632',
                    backgroundColor: 'rgba(26, 86, 50, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#1a5632',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { 
                        callbacks: {
                            label: function(context) {
                                return `Score: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: '#e5e7eb' },
                        title: { display: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
