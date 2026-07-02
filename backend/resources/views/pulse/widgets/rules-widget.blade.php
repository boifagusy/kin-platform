<div class="card">
    <div class="card-header">
        <h5>🔍 Active Detection Rules</h5>
    </div>
    <div class="card-body">
        <div class="list-group">
            @foreach($rules ?? [] as $rule)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-{{ $rule['severity'] === 'critical' ? 'danger' : 
                                                   ($rule['severity'] === 'high' ? 'warning' : 
                                                   ($rule['severity'] === 'medium' ? 'info' : 'secondary')) }}">
                            {{ ucfirst($rule['severity']) }}
                        </span>
                        {{ $rule['description'] }}
                    </div>
                    <span class="badge bg-primary rounded-pill">-{{ $rule['impact'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
