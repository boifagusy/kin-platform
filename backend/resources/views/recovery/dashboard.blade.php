@extends('layouts.admin')

@section('title', 'Recovery Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">🔄 Recovery Engine</h1>
            <p class="text-muted">Self-healing orchestration</p>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Total Attempts</h6>
                    <h3>{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Success Rate</h6>
                    <h3>{{ $stats['success_rate'] }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Failed</h6>
                    <h3 class="text-danger">{{ $stats['failed'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>Escalated</h6>
                    <h3 class="text-warning">{{ $stats['escalated'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Attempts -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Recovery Attempts</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Subsystem</th>
                                <th>Duration</th>
                                <th>Escalated</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent as $attempt)
                            <tr>
                                <td>{{ $attempt->id }}</td>
                                <td>{{ $attempt->action->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $attempt->status === 'success' ? 'success' : ($attempt->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ $attempt->status }}
                                    </span>
                                </td>
                                <td>{{ $attempt->subsystem ?? 'N/A' }}</td>
                                <td>{{ $attempt->duration_ms ? $attempt->duration_ms . 'ms' : 'N/A' }}</td>
                                <td>{{ $attempt->escalated ? 'Yes' : 'No' }}</td>
                                <td>{{ $attempt->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
