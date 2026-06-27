@extends('layouts.admin')

@section('title', 'Alert Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.alerts.index') }}" class="flex items-center gap-2 text-primary">← Back to Alerts</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold mb-4">Alert Details</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="text-gray-500">Alert ID</dt><dd>#{{ $alert->id }}</dd>
                    <dt class="text-gray-500">Type</dt><dd>{{ strtoupper($alert->escalation_type ?? 'SOS') }}</dd>
                    <dt class="text-gray-500">Priority</dt><dd>{{ strtoupper($alert->priority ?? 'N/A') }}</dd>
                    <dt class="text-gray-500">Status</dt><dd>{{ strtoupper($alert->status ?? 'ACTIVE') }}</dd>
                    <dt class="text-gray-500">User</dt><dd>{{ $alert->user->name ?? 'Unknown' }} ({{ $alert->user->phone ?? 'N/A' }})</dd>
                    <dt class="text-gray-500">Assigned To</dt><dd>{{ $alert->assignedAdmin->name ?? 'Unassigned' }}</dd>
                    <dt class="text-gray-500">Created</dt><dd>{{ $alert->created_at->format('M d, Y H:i:s') }}</dd>
                    @if($alert->resolved_at)<dt class="text-gray-500">Resolved</dt><dd>{{ \Carbon\Carbon::parse($alert->resolved_at)->format('M d, Y H:i:s') }}</dd>@endif
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold mb-4">Timeline</h2>
                <div class="space-y-3">
                    <div class="flex items-start gap-3"><div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">🚨</div><div><p class="font-medium">Alert Created</p><p class="text-sm text-gray-500">{{ $alert->created_at->format('M d, Y H:i:s') }}</p></div></div>
                    @if($alert->assigned_admin_id)<div class="flex items-start gap-3"><div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">👤</div><div><p class="font-medium">Assigned</p><p class="text-sm text-gray-500">{{ $alert->updated_at->format('M d, Y H:i:s') }}</p></div></div>@endif
                    @if($alert->resolved_at)<div class="flex items-start gap-3"><div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">✅</div><div><p class="font-medium">Resolved</p><p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($alert->resolved_at)->format('M d, Y H:i:s') }}</p></div></div>@endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold mb-4">Actions</h2>
                <div class="space-y-3">
                    @if(!$alert->assigned_admin_id)<button onclick="assignAlert()" class="w-full bg-primary text-white px-4 py-2 rounded-lg">Assign to Me</button>@endif
                    @if($alert->status != 'resolved')<button onclick="resolveAlert()" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg">Resolve Alert</button>@endif
                    <button onclick="escalateAlert()" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg">Escalate Priority</button>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold mb-4">Add Note</h2>
                <textarea id="noteText" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter note..."></textarea>
                <button onclick="addNote()" class="mt-3 bg-primary text-white px-4 py-2 rounded-lg w-full">Add Note</button>
                <div id="notesList" class="mt-4 space-y-3">
                    @foreach($notes as $note)
                    <div class="border-l-2 border-primary pl-3 py-1"><p class="text-sm">{{ $note->note }}</p><p class="text-xs text-gray-400">- {{ $note->admin->name ?? 'Admin' }} at {{ $note->created_at->format('M d, H:i') }}</p></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const alertId = {{ $alert->id }};
function assignAlert() { fetch(`/admin/alerts/${alertId}/assign`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => location.reload()); }
function resolveAlert() { let note = prompt('Resolution note:'); if(note) fetch(`/admin/alerts/${alertId}/resolve`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ note }) }).then(() => location.reload()); }
function escalateAlert() {
    if (confirm("Escalate this alert to the next priority level?")) {
        fetch("/admin/alerts/" + alertId + "/escalate", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                "Content-Type": "application/json"
            }
        }).then(response => {
            if (response.ok) {
                alert("Priority escalated successfully");
                location.reload();
            } else {
                alert("Failed to escalate. Please try again.");
            }
        }).catch(error => {
            alert("Error: " + error);
        });
    }
}
</script>
@endsection

<script>
const alertId = {{ $alert->id }};

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function assignAlert() {
    fetch(`/admin/alerts/${alertId}/assign`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            alert('Alert assigned to you');
            location.reload();
        } else {
            alert('Failed to assign');
        }
    });
}

function resolveAlert() {
    let note = prompt('Resolution note:');
    if (note) {
        fetch(`/admin/alerts/${alertId}/resolve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ note: note })
        }).then(response => {
            if (response.ok) {
                alert('Alert resolved');
                location.reload();
            } else {
                alert('Failed to resolve');
            }
        });
    }
}

function escalateAlert() {
    if (confirm('Escalate this alert to the next priority level?')) {
        fetch(`/admin/alerts/${alertId}/escalate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                alert('Priority escalated successfully');
                location.reload();
            } else {
                alert('Failed to escalate. HTTP status: ' + response.status);
            }
        }).catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

function addNote() {
    let note = document.getElementById('noteText').value;
    if (note) {
        fetch(`/admin/alerts/${alertId}/note`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ note: note })
        }).then(response => {
            if (response.ok) {
                document.getElementById('noteText').value = '';
                location.reload();
            } else {
                alert('Failed to add note');
            }
        });
    }
}
</script>
