@extends('layouts.admin')

@section('title', 'Notification Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-green-600 hover:text-green-700 flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Back to Settings
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Notification Settings</h1>
        <p class="text-sm text-gray-500">Control how users receive notifications.</p>
    </div>

    <div id="message" class="hidden mb-4 p-4 rounded-lg"></div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">SMS Notifications</p>
                    <p class="text-xs text-gray-500">Send SMS alerts and OTPs</p>
                </div>
                <div>
                    <span id="sms_status" class="mr-3 text-sm font-medium text-gray-600">{{ ($settings['sms_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}</span>
                    <button type="button" onclick="toggleSetting('sms_enabled')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ ($settings['sms_enabled'] ?? false) ? 'bg-green-600' : 'bg-gray-300' }}">
                        <span id="sms_slider" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ ($settings['sms_enabled'] ?? false) ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">WhatsApp Notifications</p>
                    <p class="text-xs text-gray-500">Send WhatsApp alerts and OTPs</p>
                </div>
                <div>
                    <span id="whatsapp_status" class="mr-3 text-sm font-medium text-gray-600">{{ ($settings['whatsapp_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}</span>
                    <button type="button" onclick="toggleSetting('whatsapp_enabled')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ ($settings['whatsapp_enabled'] ?? false) ? 'bg-green-600' : 'bg-gray-300' }}">
                        <span id="whatsapp_slider" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ ($settings['whatsapp_enabled'] ?? false) ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notification Driver</label>
                <select id="notification_driver" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    <option value="log" {{ ($settings['notification_driver'] ?? 'log') == 'log' ? 'selected' : '' }}>Log Driver (Development)</option>
                    <option value="sms" {{ ($settings['notification_driver'] ?? 'log') == 'sms' ? 'selected' : '' }}>SMS Driver (Termii - Production)</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">Select driver for sending notifications. Use Log for testing without real SMS costs.</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button onclick="saveSettings()" id="saveBtn" class="px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white font-semibold rounded-xl transition-all">Save Changes</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let sms = {{ ($settings['sms_enabled'] ?? false) ? 'true' : 'false' }};
    let whatsapp = {{ ($settings['whatsapp_enabled'] ?? false) ? 'true' : 'false' }};

    function toggleSetting(setting) {
        let value;
        if (setting === 'sms_enabled') { sms = !sms; value = sms; }
        else { whatsapp = !whatsapp; value = whatsapp; }
        
        const btn = event.currentTarget;
        const slider = document.getElementById(setting + '_slider');
        const status = document.getElementById(setting.replace('_enabled', '_status'));
        
        if (value) {
            btn.classList.remove('bg-gray-300'); btn.classList.add('bg-green-600');
            slider.classList.remove('translate-x-1'); slider.classList.add('translate-x-6');
            status.textContent = 'Enabled';
        } else {
            btn.classList.remove('bg-green-600'); btn.classList.add('bg-gray-300');
            slider.classList.remove('translate-x-6'); slider.classList.add('translate-x-1');
            status.textContent = 'Disabled';
        }
    }

    async function saveSettings() {
        const btn = document.getElementById('saveBtn');
        const msg = document.getElementById('message');
        const original = btn.innerHTML;
        
        btn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div> Saving...';
        btn.disabled = true;
        
        const data = {
            sms_enabled: sms,
            whatsapp_enabled: whatsapp,
            notification_driver: document.getElementById('notification_driver').value
        };
        
        try {
            const res = await fetch('/admin/settings/notifications', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                btn.innerHTML = '✓ Saved!';
                btn.classList.add('bg-green-600');
                msg.classList.remove('hidden');
                msg.className = 'mb-4 p-4 rounded-lg bg-green-50 border border-green-200';
                msg.innerHTML = '<p class="text-green-800">✓ Notification settings saved!</p>';
                setTimeout(() => { msg.classList.add('hidden'); }, 3000);
                setTimeout(() => { btn.innerHTML = original; btn.classList.remove('bg-green-600'); btn.disabled = false; }, 1500);
            } else {
                throw new Error('Failed');
            }
        } catch(e) {
            btn.innerHTML = '✗ Error!';
            btn.classList.add('bg-red-600');
            msg.classList.remove('hidden');
            msg.className = 'mb-4 p-4 rounded-lg bg-red-50 border border-red-200';
            msg.innerHTML = '<p class="text-red-800">✗ Failed to save. Please try again.</p>';
            setTimeout(() => { msg.classList.add('hidden'); }, 3000);
            setTimeout(() => { btn.innerHTML = original; btn.classList.remove('bg-red-600'); btn.disabled = false; }, 2000);
        }
    }
</script>
@endpush
