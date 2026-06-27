@extends('layouts.admin')

@section('title', 'Feature Flags')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-green-600 hover:text-green-700 flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Back to Settings
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Feature Flags</h1>
        <p class="text-sm text-gray-500">Enable or disable platform features (Coming soon).</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
        <span class="material-symbols-outlined text-5xl text-gray-400 mb-3">flag</span>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Coming Soon</h3>
        <p class="text-gray-500">Feature flags for gradual rollouts will appear here.</p>
    </div>
</div>
@endsection
