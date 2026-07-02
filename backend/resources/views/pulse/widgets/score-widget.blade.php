<div class="card">
    <div class="card-header">
        <h5>📊 Safety Score</h5>
    </div>
    <div class="card-body text-center">
        <div style="font-size: 48px; font-weight: bold; color: {{ $scoreColor ?? '#28a745' }}">
            {{ $score ?? 0 }}
        </div>
        <div style="font-size: 18px; color: {{ $scoreColor ?? '#28a745' }}">
            {{ $levelLabel ?? 'Safe' }}
        </div>
        <div class="progress mt-3" style="height: 10px;">
            <div class="progress-bar bg-{{ $levelColor ?? 'success' }}" 
                 style="width: {{ $score ?? 0 }}%;"></div>
        </div>
        <small class="text-muted mt-2 d-block">Trend: {{ $trend ?? 'stable' }}</small>
    </div>
</div>
