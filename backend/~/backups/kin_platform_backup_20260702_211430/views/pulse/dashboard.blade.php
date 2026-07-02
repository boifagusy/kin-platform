@extends('layouts.admin')

@section('title', 'Pulse Safety Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">🛡️ Pulse Safety Dashboard</h1>
            <p class="text-muted">Real-time safety intelligence for all users</p>
        </div>
    </div>
    
    <!-- Stats Row -->
    <div class="row">
        <div class="col-md-3">
            @include('pulse.widgets.score-widget', [
                'score' => $avgScore ?? 0,
                'scoreColor' => $avgScore >= 80 ? '#28a745' : ($avgScore >= 50 ? '#ffc107' : ($avgScore >= 30 ? '#fd7e14' : '#dc3545')),
                'levelLabel' => $avgScore >= 80 ? 'Safe' : ($avgScore >= 50 ? 'Monitor' : ($avgScore >= 30 ? 'At Risk' : 'Emergency')),
                'levelColor' => $avgScore >= 80 ? 'success' : ($avgScore >= 50 ? 'warning' : ($avgScore >= 30 ? 'warning' : 'danger')),
                'trend' => 'stable'
            ])
        </div>
        <div class="col-md-3">
            @include('pulse.widgets.emergency-widget', [
                'emergencyCount' => $emergencyCount ?? 0,
                'atRiskCount' => $atRiskCount ?? 0,
                'safeCount' => $totalUsers - ($emergencyCount ?? 0) - ($atRiskCount ?? 0)
            ])
        </div>
        <div class="col-md-6">
            @include('pulse.widgets.rules-widget', ['rules' => $activeRules ?? []])
        </div>
    </div>
    
    <!-- Users Table Row -->
    <div class="row mt-4">
        <div class="col-12">
            @include('pulse.widgets.users-widget', ['users' => $userScores ?? []])
        </div>
    </div>
</div>
@endsection
