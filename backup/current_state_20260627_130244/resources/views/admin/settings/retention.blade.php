@extends('layouts.admin')

@section('title', 'Data Retention')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-green-600 hover:text-green-700 flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Back to Settings
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Data Retention</h1>
        <p class="text-sm text-gray-500">Configure how long different types of data are kept in the system.</p>
    </div>

    <div id="message" class="hidden mb-4 p-4 rounded-lg"></div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 space-y-6">
            <!-- User Data Retention -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-green-600">group</span>
                    <h3 class="text-base font-semibold text-gray-800">User Data Retention</h3>
                </div>
                <p class="text-sm text-gray-500 mb-4">Configure how long user data is retained after account deletion.</p>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Inactive User Cleanup (Days)</label>
                        <input type="number" id="inactive_user_cleanup_days" value="{{ $settings['inactive_user_cleanup_days'] ?? 90 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <p class="text-xs text-gray-400 mt-1">Delete users inactive for more than X days (0 = never)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Deleted Account Retention (Days)</label>
                        <input type="number" id="deleted_account_retention_days" value="{{ $settings['deleted_account_retention_days'] ?? 30 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <p class="text-xs text-gray-400 mt-1">How long to keep deleted account data before permanent removal</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            <!-- Logs Retention -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-green-600">receipt_long</span>
                    <h3 class="text-base font-semibold text-gray-800">Logs Retention</h3>
                </div>
                <p class="text-sm text-gray-500 mb-4">Configure how long various logs are retained.</p>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">OTP Logs Retention (Days)</label>
                        <input type="number" id="retention_otp_days" value="{{ $settings['retention_otp_days'] ?? 90 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <p class="text-xs text-gray-400 mt-1">OTP generation and verification logs</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Audit Logs Retention (Days)</label>
                        <input type="number" id="retention_audit_days" value="{{ $settings['retention_audit_days'] ?? 365 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <p class="text-xs text-gray-400 mt-1">Admin audit logs (recommended: 365 days)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Activity Logs Retention (Days)</label>
                        <input type="number" id="activity_logs_retention_days" value="{{ $settings['activity_logs_retention_days'] ?? 180 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <p class="text-xs text-gray-400 mt-1">User activity logs (check-ins, SOS, etc.)</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            <!-- Backup Settings -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-green-600">backup</span>
                    <h3 class="text-base font-semibold text-gray-800">Backup Settings</h3>
                </div>
                <p class="text-sm text-gray-500 mb-4">Configure backup retention policies.</p>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Database Backup Retention (Days)</label>
                        <input type="number" id="backup_retention_days" value="{{ $settings['backup_retention_days'] ?? 30 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <p class="text-xs text-gray-400 mt-1">How long to keep database backups</p>
                    </div>
                </div>
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
    async function saveSettings() {
        const btn = document.getElementById('saveBtn');
        const msg = document.getElementById('message');
        const original = btn.innerHTML;
        
        btn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div> Saving...';
        btn.disabled = true;
        
        const data = {
            retention_otp_days: parseInt(document.getElementById('retention_otp_days').value),
            retention_audit_days: parseInt(document.getElementById('retention_audit_days').value),
            inactive_user_cleanup_days: parseInt(document.getElementById('inactive_user_cleanup_days').value),
            deleted_account_retention_days: parseInt(document.getElementById('deleted_account_retention_days').value),
            activity_logs_retention_days: parseInt(document.getElementById('activity_logs_retention_days').value),
            backup_retention_days: parseInt(document.getElementById('backup_retention_days').value)
        };
        
        try {
            const res = await fetch('/admin/settings/retention', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            
            if (result.success) {
                btn.innerHTML = '✓ Saved!';
                btn.classList.remove('bg-green-700');
                btn.classList.add('bg-green-600');
                msg.classList.remove('hidden');
                msg.className = 'mb-4 p-4 rounded-lg bg-green-50 border border-green-200';
                msg.innerHTML = '<p class="text-green-800">✓ Retention settings saved successfully!</p>';
                setTimeout(() => { msg.classList.add('hidden'); }, 3000);
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
            btn.innerHTML = '✗ Error!';
            btn.classList.add('bg-red-600');
            msg.classList.remove('hidden');
            msg.className = 'mb-4 p-4 rounded-lg bg-red-50 border border-red-200';
            msg.innerHTML = '<p class="text-red-800">✗ Failed to save. Please try again.</p>';
            setTimeout(() => { msg.classList.add('hidden'); }, 3000);
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
