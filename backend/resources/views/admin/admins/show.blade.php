@extends('layouts.admin')
@section('title', 'Admin Details')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Admin Details</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.admins.edit', $admin->id) }}" class="bg-primary text-white px-4 py-2 rounded-lg">Edit</a>
            <a href="{{ route('admin.admins.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg">Back</a>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div><strong>ID:</strong> {{ $admin->id }}</div>
        <div><strong>Name:</strong> {{ $admin->name }}</div>
        <div><strong>Email:</strong> {{ $admin->email }}</div>
        <div><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $admin->role)) }}</div>
        <div><strong>Status:</strong> {{ $admin->is_active ? 'Active' : 'Inactive' }}</div>
        <div><strong>Last Login:</strong> {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</div>
        <div><strong>Created:</strong> {{ $admin->created_at->format('M d, Y H:i:s') }}</div>
        <div><strong>Updated:</strong> {{ $admin->updated_at->format('M d, Y H:i:s') }}</div>
    </div>
</div>
@endsection
