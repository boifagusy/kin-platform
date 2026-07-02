<div class="card border-danger">
    <div class="card-header bg-danger text-white">
        <h5>🚨 Active Emergencies</h5>
    </div>
    <div class="card-body text-center">
        <div style="font-size: 48px; font-weight: bold; color: #dc3545;">
            {{ $emergencyCount ?? 0 }}
        </div>
        <div style="font-size: 14px; color: #6c757d;">
            Users in Emergency status
        </div>
        <div style="font-size: 12px; color: #6c757d; margin-top: 10px;">
            At Risk: {{ $atRiskCount ?? 0 }} | Safe: {{ $safeCount ?? 0 }}
        </div>
    </div>
</div>
