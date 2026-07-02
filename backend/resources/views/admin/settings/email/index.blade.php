@extends('layouts.admin')

@section('title', 'Email Settings')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Email Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure SMTP for password reset and notifications</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.settings.email.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mail Driver</label>
                    <select name="mail_mailer" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="smtp" {{ old('mail_mailer', $settings['mail_mailer'] ?? 'log') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendmail" {{ old('mail_mailer', $settings['mail_mailer'] ?? 'log') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="log" {{ old('mail_mailer', $settings['mail_mailer'] ?? 'log') == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.gmail.com') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Port</label>
                    <input type="text" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                    <select name="mail_encryption" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="tls" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') == '' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="mail_password" value="" placeholder="Leave blank to keep current" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Email</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'noreply@kin.com') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'KIN Safety') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-container">Save Settings</button>
                <button type="button" id="testEmailBtn" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Send Test Email</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('testEmailBtn').addEventListener('click', async function() {
    const response = await fetch('{{ route("admin.settings.email.test") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    });
    const data = await response.json();
    alert(data.message);
});
</script>
@endsection
