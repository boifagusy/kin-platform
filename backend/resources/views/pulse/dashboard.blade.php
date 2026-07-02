@extends('layouts.admin')

@section('title', 'Pulse Safety')

@section('content')
@php
    try {
        $scoreService = app()->make(\App\Services\Pulse\SafetyScoreService::class);
        $status = $scoreService->getSafetyStatus();
        $users = \App\Models\User::all();
        $userScores = [];
        $emergencyUsers = [];
        $atRiskUsers = [];
        $safeUsers = [];
        
        foreach ($users as $user) {
            $score = $scoreService->calculateScore($user);
            $level = $scoreService->getLevel($score);
            $userScores[$user->id] = ['name' => $user->name, 'score' => $score, 'level' => $level];
            if ($level === 'emergency') { $emergencyUsers[] = $user->name; }
            elseif ($level === 'at_risk') { $atRiskUsers[] = $user->name; }
            else { $safeUsers[] = $user->name; }
        }
        
        $recentEvents = \App\Models\SafetyEvent::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    } catch (\Exception $e) {
        $status = ['score' => 0, 'emergency_count' => 0, 'at_risk_count' => 0, 'total_users' => 0];
        $userScores = [];
        $emergencyUsers = [];
        $atRiskUsers = [];
        $safeUsers = [];
        $recentEvents = collect();
    }
@endphp

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">💓 Pulse Safety</h1>
        <p class="text-gray-500 mt-1">Real-time safety intelligence</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Average Safety Score</p>
            <p class="text-2xl font-bold text-{{ ($status['score'] ?? 0) >= 80 ? 'green-600' : (($status['score'] ?? 0) >= 50 ? 'yellow-600' : 'red-600') }}">
                {{ $status['score'] ?? 0 }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Users Monitored</p>
            <p class="text-2xl font-bold text-gray-800">{{ $status['total_users'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Active Emergencies</p>
            <p class="text-2xl font-bold text-red-600">{{ $status['emergency_count'] ?? 0 }}</p>
            @if(count($emergencyUsers) > 0)
                <p class="text-red-500 text-xs">Users: {{ implode(', ', $emergencyUsers) }}</p>
            @endif
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">At Risk Users</p>
            <p class="text-2xl font-bold text-orange-600">{{ $status['at_risk_count'] ?? 0 }}</p>
        </div>
    </div>

    <!-- User Safety Table -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">👤 User Safety Status</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 text-gray-600 font-semibold">User</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-semibold">Score</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-semibold">Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userScores as $data)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $data['name'] }}</td>
                        <td class="py-3 px-4 font-bold text-{{ $data['score'] >= 80 ? 'green-600' : ($data['score'] >= 50 ? 'yellow-600' : 'red-600') }}">
                            {{ $data['score'] }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs text-white bg-{{ $data['level'] === 'safe' ? 'green-500' : ($data['level'] === 'monitor' ? 'yellow-500' : ($data['level'] === 'at_risk' ? 'orange-500' : 'red-500')) }}">
                                {{ strtoupper($data['level']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">📋 Recent Safety Events</h2>
        @if($recentEvents->count() > 0)
            <div class="space-y-2">
                @foreach($recentEvents as $event)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium text-gray-700">{{ $event->event_type }}</span>
                            <span class="text-gray-500 text-sm ml-2">User #{{ $event->user_id }}</span>
                        </div>
                        <span class="text-gray-400 text-sm">{{ $event->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No recent safety events</p>
        @endif
    </div>
</div>
@endsection
