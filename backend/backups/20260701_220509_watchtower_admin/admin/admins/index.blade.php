@extends('layouts.admin')

@section('title', 'Admin Management')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Admin Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage administrator accounts</p>
        </div>
        <a href="{{ route('admin.admins.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-container">
            + New Admin
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($admins as $admin)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">#{{ $admin->id }}</td>
                        <td class="px-6 py-4 font-medium">{{ $admin->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $admin->email }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $admin->role === 'super_admin' ? 'bg-gold/20 text-gold' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($admin->is_active)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $admin->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.admins.show', $admin->id) }}" class="text-primary hover:underline">View</a>
                                <a href="{{ route('admin.admins.edit', $admin->id) }}" class="text-primary hover:underline">Edit</a>
                                @if($admin->is_active)
                                <form method="POST" action="{{ route('admin.admins.deactivate', $admin->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Deactivate this admin?')">Deactivate</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.admins.activate', $admin->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:underline" onclick="return confirm('Activate this admin?')">Activate</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">No admin users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $admins->links() }}
        </div>
    </div>
</div>
@endsection
