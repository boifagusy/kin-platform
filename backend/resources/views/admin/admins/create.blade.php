@extends('layouts.admin')
@section('title', 'Create Admin')
@php $permissionService = app(\App\Services\Admin\PermissionService::class); @endphp
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Create New Admin</h1>
    <form method="POST" action="{{ route('admin.admins.store') }}">
        @csrf
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" id="role-select" onchange="loadDefaultPermissions()" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">-- Select Role --</option>
                    @foreach($permissionService->getAvailableRoles() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Active</span>
                </label>
            </div>

            <div id="permissions-section" class="border-t pt-4">
                <h3 class="font-semibold text-sm mb-3">Custom Permissions (optional — role defaults auto-loaded)</h3>
                @foreach($permissionService->getPermissionGroups() as $group => $permissions)
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ $group }}</p>
                        <div class="grid grid-cols-2 gap-1">
                            @foreach($permissions as $perm)
                                <label class="flex items-center gap-1 text-xs">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="perm-checkbox">
                                    {{ str_replace('.', ' → ', $perm) }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg">Create Admin</button>
            <a href="{{ route('admin.admins.index') }}" class="ml-2 text-gray-600">Cancel</a>
        </div>
    </form>
</div>

<script>
const rolePermissions = @json(array_map(fn($p) => $p, $permissionService->rolePermissions));
function loadDefaultPermissions() {
    const role = document.getElementById('role-select').value;
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    if (role && rolePermissions[role]) {
        rolePermissions[role].forEach(perm => {
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                if (cb.value === perm) cb.checked = true;
            });
        });
    }
}
</script>
@endsection
