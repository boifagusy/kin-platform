@extends('layouts.admin')

@section('title', 'User Details - ' . ($user->name ?? 'Unknown'))

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button and Edit Button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 text-primary hover:text-primary-container transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Back to Users
        </a>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-container flex items-center gap-2 ml-auto">
            <span class="material-symbols-outlined text-sm">edit</span>
            Edit User
        </a>
    </div>

    <!-- Admin Actions Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Admin Actions</h2>
        <div class="flex flex-wrap gap-3">
            @if($user->deleted_at)
            <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" onsubmit="return confirm('Restore this user?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">restore</span>
                    Restore User
                </button>
            </form>
            @endif

            @if($user->status == 'suspended')
            <button onclick="activateUser({{ $user->id }})" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                Activate Account
            </button>
            @elseif(!$user->deleted_at)
            <button onclick="showSuspendModal({{ $user->id }})" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">block</span>
                Suspend Account
            </button>
            @endif
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left Column: Profile Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Profile</h2>
            <div class="flex items-start gap-6">
                <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center text-primary text-2xl font-bold">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800">{{ $user->name ?? 'Unknown' }}</h3>
                    <p class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-sm">email</span>
                        {{ $user->email }}
                    </p>
                    @if($user->phone)
                    <p class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-sm">call</span>
                        {{ $user->phone }}
                    </p>
                    @endif
                    <div class="mt-2 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-50 text-green-700 text-xs">
                        <span class="material-symbols-outlined text-sm">verified</span>
                        Verified Account
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">Member since: {{ $user->created_at->format('M d, Y') }}</p>
                <p class="text-sm text-gray-500">Last login: {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}</p>
            </div>
        </div>

        <!-- Right Column: Safety Risk Assessment -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Safety Risk Assessment</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Safety Score</span>
                        <span class="font-bold {{ $user->safety_score >= 80 ? 'text-green-600' : ($user->safety_score >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $user->safety_score }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $user->safety_score >= 80 ? 'bg-green-500' : ($user->safety_score >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $user->safety_score }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div>
                        <p class="text-xs text-gray-500">Last Check-In</p>
                        <p class="text-sm font-medium">{{ $user->last_checkin ? $user->last_checkin->checked_in_at->diffForHumans() : 'Never' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Next Check-In</p>
                        <p class="text-sm font-medium">{{ $user->next_checkin ? $user->next_checkin->format('M d, h:i A') : 'Not scheduled' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Missed (30d)</p>
                        <p class="text-sm font-medium {{ $user->missed_checkins_30d > 0 ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $user->missed_checkins_30d }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">SOS Events (30d)</p>
                        <p class="text-sm font-medium {{ $user->sos_count_30d > 0 ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $user->sos_count_30d }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Duress Events (30d)</p>
                        <p class="text-sm font-medium {{ $user->duress_count_30d > 0 ? 'text-orange-600' : 'text-gray-800' }}">
                            {{ $user->duress_count_30d }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Risk Level</p>
                        <p class="text-sm font-bold {{ $user->risk_level == 'LOW' ? 'text-green-600' : ($user->risk_level == 'MEDIUM' ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $user->risk_level }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kin Circle (Trusted Contacts) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Kin Circle</h2>
            @if($user->trusted_contacts && $user->trusted_contacts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($user->trusted_contacts as $index => $contact)
                    <div class="p-4 border rounded-lg {{ $index == 0 ? 'border-primary/30 bg-primary/5' : 'border-gray-200' }}">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold">
                                {{ strtoupper(substr($contact->name ?? 'C', 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ $contact->name }}
                                    @if($index == 0)
                                        <span class="text-xs px-2 py-0.5 bg-primary text-white rounded-full ml-2">Primary</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">{{ $contact->relationship ?? 'Contact' }}</p>
                                <p class="text-sm text-gray-500">{{ $contact->phone }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No trusted contacts added yet.</p>
            @endif
        </div>

        <!-- Last Safe State -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Last Safe State</h2>
                <p class="text-sm text-gray-500">Recorded {{ $user->last_checkin ? $user->last_checkin->checked_in_at->diffForHumans() : 'Never' }}</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-gray-100 bg-gray-50">
                <div class="p-6 flex flex-col items-center text-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">location_on</span>
                    <p class="text-xs text-gray-500">Location</p>
                    <p class="text-sm font-medium">{{ $user->last_checkin && $user->last_checkin->latitude ? 'Tracked' : 'Unknown' }}</p>
                </div>
                <div class="p-6 flex flex-col items-center text-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">battery_6_bar</span>
                    <p class="text-xs text-gray-500">Battery</p>
                    <p class="text-sm font-medium">{{ $user->last_checkin && $user->last_checkin->battery_level ? $user->last_checkin->battery_level . '%' : 'Unknown' }}</p>
                </div>
                <div class="p-6 flex flex-col items-center text-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">signal_cellular_alt</span>
                    <p class="text-xs text-gray-500">Signal</p>
                    <p class="text-sm font-medium">Unknown</p>
                </div>
                <div class="p-6 flex flex-col items-center text-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">directions_walk</span>
                    <p class="text-xs text-gray-500">Movement</p>
                    <p class="text-sm font-medium">Unknown</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 90%; margin: auto; padding: 24px;">
        <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 16px;">Suspend User Account</h3>
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 4px;">Reason *</label>
            <textarea id="suspendReason" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 8px;" placeholder="Why is this user being suspended?"></textarea>
        </div>
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 4px;">Additional Notes</label>
            <textarea id="suspendNotes" rows="2" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 8px;" placeholder="Any additional context..."></textarea>
        </div>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button onclick="closeSuspendModal()" style="padding: 8px 16px; border: 1px solid #ccc; border-radius: 8px; background: white; cursor: pointer;">Cancel</button>
            <button onclick="submitSuspend()" style="padding: 8px 16px; border: none; border-radius: 8px; background: #dc2626; color: white; cursor: pointer;">Suspend User</button>
        </div>
    </div>
</div>

<script>
let currentUserId = null;

function showSuspendModal(userId) {
    currentUserId = userId;
    document.getElementById('suspendModal').style.display = 'flex';
    document.getElementById('suspendReason').value = '';
    document.getElementById('suspendNotes').value = '';
}

function closeSuspendModal() {
    document.getElementById('suspendModal').style.display = 'none';
    currentUserId = null;
}

function submitSuspend() {
    const reason = document.getElementById('suspendReason').value;
    const notes = document.getElementById('suspendNotes').value;
    
    if (!reason.trim()) {
        alert('Please provide a reason for suspension');
        return;
    }
    
    fetch('/admin/users/' + currentUserId + '/suspend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ reason: reason, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success === true || data.success === "true") {
            // Redirect back to the user detail page
            window.location.href = '/admin/users/' + currentUserId;
        } else {
            alert('Failed to suspend user: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function activateUser(userId) {
    if (!confirm('Are you sure you want to activate this user?')) return;
    
    fetch('/admin/users/' + userId + '/activate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success === true || data.success === "true") {
            window.location.href = '/admin/users/' + userId;
        } else {
            alert('Failed to activate user: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
@endsection
