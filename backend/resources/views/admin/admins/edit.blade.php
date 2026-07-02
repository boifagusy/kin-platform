@extends('layouts.admin')
@section('title', 'Edit Admin')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Admin</h1>
    <form method="POST" action="{{ route('admin.admins.update', $admin->id) }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ $admin->name }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ $admin->email }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="support_admin" {{ $admin->role === 'support_admin' ? 'selected' : '' }}>Support Admin</option>
                    <option value="viewer_admin" {{ $admin->role === 'viewer_admin' ? 'selected' : '' }}>Viewer Admin</option>
                    <option value="super_admin" {{ $admin->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $admin->is_active ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg">Update Admin</button>
            <a href="{{ route('admin.admins.index') }}" class="ml-2 text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection
