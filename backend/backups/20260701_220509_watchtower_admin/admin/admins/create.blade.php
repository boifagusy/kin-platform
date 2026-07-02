@extends('layouts.admin')
@section('title', 'Create Admin')
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
                <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="support_admin">Support Admin</option>
                    <option value="viewer_admin">Viewer Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Active</span>
                </label>
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg">Create Admin</button>
            <a href="{{ route('admin.admins.index') }}" class="ml-2 text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection
