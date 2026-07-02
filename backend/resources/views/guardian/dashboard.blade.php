@extends('layouts.admin')

@section('title', 'Guardian Platform')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">🛡️ Guardian Platform</h1>
            <p class="text-muted">Unified view of all KIN subsystems</p>
            <small class="text-muted">Last updated: {{ $lastUpdated }}</small>
        </div>
    </div>
    
    <!-- Guardian Score -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card bg-{{ $guardianScore['overall'] >= 80 ? 'success' : ($guardianScore['overall'] >= 60 ? 'warning' : 'danger') }} text-white">
                <div class="card-body text-center">
                    <h5>Guardian Score</h5>
                    <div style="font-size: 72px; font-weight: bold;">
                        {{ $guardianScore['overall'] }}
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <small>Operations</small>
                            <div>{{ $guardianScore['operations'] }}</div>
                        </div>
                        <div class="col-md-4">
                            <small>Security</small>
                            <div>{{ $guardianScore['security'] }}</div>
                        </div>
                        <div class="col-md-4">
                            <small>Safety</small>
                            <div>{{ $guardianScore['safety'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Platform Health</h6>
                    <h3 class="text-{{ $platformStatus['health']['status'] === 'healthy' ? 'success' : 'danger' }}">
                        {{ ucfirst($platformStatus['health']['status'] ?? 'Unknown') }}
                    </h3>
                    <small class="text-muted">Score: {{ $platformStatus['health']['score'] ?? 0 }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Security Status</h6>
                    <h3 class="text-{{ $platformStatus['security']['status'] === 'normal' ? 'success' : 'warning' }}">
                        {{ ucfirst($platformStatus['security']['status'] ?? 'Unknown') }}
                    </h3>
                    <small class="text-muted">Score: {{ $platformStatus['security']['score'] ?? 0 }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Safety Status</h6>
                    <h3 class="text-{{ $platformStatus['safety']['status'] === 'normal' ? 'success' : 'warning' }}">
                        {{ ucfirst($platformStatus['safety']['status'] ?? 'Unknown') }}
                    </h3>
                    <small class="text-muted">Score: {{ $platformStatus['safety']['score'] ?? 0 }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Active Incidents</h6>
                    <h3 class="text-{{ ($platformStatus['incidents']['critical'] ?? 0) > 0 ? 'danger' : 'success' }}">
                        {{ $platformStatus['incidents']['total'] ?? 0 }}
                    </h3>
                    <small class="text-muted">
                        Critical: {{ $platformStatus['incidents']['critical'] ?? 0 }} | 
                        High: {{ $platformStatus['incidents']['high'] ?? 0 }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Timeline -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>📋 Unified Timeline</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if(empty($timeline))
                        <p class="text-muted text-center">No events yet</p>
                    @else
                        <div class="timeline">
                            @foreach($timeline as $event)
                                <div class="timeline-item d-flex mb-3">
                                    <div class="timeline-badge me-3">
                                        <span class="badge bg-{{ $event['severity'] === 'critical' ? 'danger' : ($event['severity'] === 'high' ? 'warning' : 'info') }}">
                                            {{ ucfirst($event['source']) }}
                                        </span>
                                    </div>
                                    <div class="timeline-content flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ $event['message'] }}</strong>
                                            <small class="text-muted">{{ $event['time_ago'] }}</small>
                                        </div>
                                        <div class="text-muted small">
                                            User: {{ $event['user'] }} | 
                                            Type: {{ $event['event_type'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
