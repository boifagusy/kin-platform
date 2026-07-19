@extends('layouts.admin')
@section('title', 'Edit Admin')
@php $permissionService = app(\App\Services\Admin\PermissionService::class); @endphp
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Admin: {{ $admin->name }}</h1>
    <form method="POST" action="{{ route('admin.admins.update', $admin->id) }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $admin->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $admin->email) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">New Password (leave blank to keep)</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" id="role-select" onchange="loadDefaultPermissions()" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach($permissionService->getAvailableRoles() as $value => $label)
                        <option value="{{ $value }}" {{ $admin->role === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $admin->is_active ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>

            @php $effectivePerms = $permissionService->getEffectivePermissions($admin); @endphp
            <div id="permissions-section" class="border-t pt-4">
                <h3 class="font-semibold text-sm mb-3">Permissions</h3>
                @foreach($permissionService->getPermissionGroups() as $group => $permissions)
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ $group }}</p>
                        <div class="grid grid-cols-2 gap-1">
                            @foreach($permissions as $perm)
                                <label class="flex items-center gap-1 text-xs">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="perm-checkbox"
                                        {{ in_array($perm, $effectivePerms) ? 'checked' : '' }}>
                                    {{ str_replace('.', ' → ', $perm) }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg">Update Admin</button>
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
