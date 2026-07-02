@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 md:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Settings</h1>
        <p class="text-sm sm:text-base text-gray-500">Manage platform configuration and preferences.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- OTP Settings -->
        <a href="{{ route('admin.settings.otp') }}" class="group block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <span class="material-symbols-outlined">password</span>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-green-700 transition-colors">OTP Settings</h3>
            </div>
            <p class="text-sm text-gray-500">Configure OTP length, expiry, and delivery channels.</p>
            <div class="mt-4 text-sm text-green-600 opacity-0 group-hover:opacity-100 transition-opacity">Configure →</div>
        </a>

        <!-- Notification Settings -->
        <a href="{{ route('admin.settings.notifications') }}" class="group block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                    <span class="material-symbols-outlined">notifications</span>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-green-700 transition-colors">Notification Settings</h3>
            </div>
            <p class="text-sm text-gray-500">Control SMS, WhatsApp, and email notifications.</p>
            <div class="mt-4 text-sm text-green-600 opacity-0 group-hover:opacity-100 transition-opacity">Configure →</div>
        </a>

        <!-- Security Settings -->
        <a href="{{ route('admin.settings.security') }}" class="group block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                    <span class="material-symbols-outlined">security</span>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-green-700 transition-colors">Security Settings</h3>
            </div>
            <p class="text-sm text-gray-500">Rate limiting, lockout policies, and security rules.</p>
            <div class="mt-4 text-sm text-green-600 opacity-0 group-hover:opacity-100 transition-opacity">Configure →</div>
        </a>

        <!-- Data Retention -->
        <a href="{{ route('admin.settings.retention') }}" class="group block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center text-yellow-600">
                    <span class="material-symbols-outlined">database</span>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-green-700 transition-colors">Data Retention</h3>
            </div>
            <p class="text-sm text-gray-500">Configure how long logs and data are kept.</p>
            <div class="mt-4 text-sm text-green-600 opacity-0 group-hover:opacity-100 transition-opacity">Configure →</div>
        </a>

        <!-- Integrations -->
        <a href="{{ route('admin.settings.integrations') }}" class="group block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                    <span class="material-symbols-outlined">integration_instructions</span>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-green-700 transition-colors">Integrations</h3>
            </div>
            <p class="text-sm text-gray-500">SMS, WhatsApp, and third-party services.</p>
            <div class="mt-4 text-sm text-green-600 opacity-0 group-hover:opacity-100 transition-opacity">Configure →</div>
        </a>

        <!-- Feature Flags -->
        <a href="{{ route('admin.settings.features') }}" class="group block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <span class="material-symbols-outlined">flag</span>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-green-700 transition-colors">Feature Flags</h3>
            </div>
            <p class="text-sm text-gray-500">Enable or disable platform features.</p>
            <div class="mt-4 text-sm text-green-600 opacity-0 group-hover:opacity-100 transition-opacity">Configure →</div>
        </a>
    </div>
</div>
@endsection
