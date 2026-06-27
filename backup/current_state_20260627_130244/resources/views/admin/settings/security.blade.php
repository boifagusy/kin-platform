@extends('layouts.admin')

@section('title', 'Security Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-green-600 hover:text-green-700 flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Back to Settings
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Security Settings</h1>
        <p class="text-sm text-gray-500">Configure rate limiting and security policies.</p>
    </div>

    <div id="message" class="hidden mb-4 p-4 rounded-lg"></div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 space-y-5">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Rate Limiting</h3>
                        <p class="text-xs text-gray-500">Prevent abuse and brute force attacks</p>
                    </div>
                    <div>
                        <span id="rate_limit_status" class="mr-3 text-sm font-medium text-gray-600">{{ ($settings['rate_limit_enabled'] ?? true) ? 'Enabled' : 'Disabled' }}</span>
                        <button type="button" onclick="toggleRateLimit()" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ ($settings['rate_limit_enabled'] ?? true) ? 'bg-green-600' : 'bg-gray-300' }}">
                            <span id="rate_limit_slider" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ ($settings['rate_limit_enabled'] ?? true) ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Attempts per Hour</label>
                        <input type="number" id="rate_limit_attempts" value="{{ $settings['rate_limit_attempts'] ?? 5 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lockout Duration (Minutes)</label>
                        <input type="number" id="rate_limit_lockout_minutes" value="{{ $settings['rate_limit_lockout_minutes'] ?? 30 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button onclick="saveSettings()" id="saveBtn" class="px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white font-semibold rounded-xl">Save Changes</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rate_limit = {{ ($settings['rate_limit_enabled'] ?? true) ? 'true' : 'false' }};

    function toggleRateLimit() {
        rate_limit = !rate_limit;
        const btn = event.currentTarget;
        const slider = document.getElementById('rate_limit_slider');
        const status = document.getElementById('rate_limit_status');
        
        if (rate_limit) {
            btn.classList.remove('bg-gray-300');
            btn.classList.add('bg-green-600');
            slider.classList.remove('translate-x-1');
            slider.classList.add('translate-x-6');
            status.textContent = 'Enabled';
        } else {
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-gray-300');
            slider.classList.remove('translate-x-6');
            slider.classList.add('translate-x-1');
            status.textContent = 'Disabled';
        }
    }

    async function saveSettings() {
        const btn = document.getElementById('saveBtn');
        const msg = document.getElementById('message');
        const original = btn.innerHTML;
        
        btn.innerHTML = 'Saving...';
        btn.disabled = true;
        
        const data = {
            rate_limit_enabled: rate_limit,
            rate_limit_attempts: parseInt(document.getElementById('rate_limit_attempts').value),
            rate_limit_lockout_minutes: parseInt(document.getElementById('rate_limit_lockout_minutes').value)
        };
        
        console.log('Sending data:', data);
        
        try {
            const res = await fetch('/admin/settings/security', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            console.log('Response:', result);
            
            if (result.success) {
                btn.innerHTML = '✓ Saved!';
                btn.classList.remove('bg-green-700');
                btn.classList.add('bg-green-600');
                msg.classList.remove('hidden');
                msg.className = 'mb-4 p-4 rounded-lg bg-green-50 border border-green-200';
                msg.innerHTML = '<p class="text-green-800">✓ Saved!</p>';
                setTimeout(() => { msg.classList.add('hidden'); }, 2000);
                setTimeout(() => {
                    btn.innerHTML = original;
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-green-700');
                    btn.disabled = false;
                }, 1500);
            } else {
                throw new Error('Failed');
            }
        } catch(e) {
            console.error('Error:', e);
            btn.innerHTML = '✗ Error!';
            btn.classList.add('bg-red-600');
            msg.classList.remove('hidden');
            msg.className = 'mb-4 p-4 rounded-lg bg-red-50 border border-red-200';
            msg.innerHTML = '<p class="text-red-800">✗ Failed to save.</p>';
            setTimeout(() => { msg.classList.add('hidden'); }, 2000);
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('bg-red-600');
                btn.classList.add('bg-green-700');
                btn.disabled = false;
            }, 2000);
        }
    }
</script>
@endpush
