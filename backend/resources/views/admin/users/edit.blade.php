@extends('layouts.admin')

@section('title', 'Edit User - ' . ($user->name ?? 'Unknown'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.show', $user->id) }}" class="flex items-center gap-2 text-primary hover:text-primary-container transition-colors">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Back to User
            </a>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Edit User Information</h2>

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $user->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending" {{ $user->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                    @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                @if($user->deleted_at)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-sm text-yellow-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">warning</span>
                        This user was deleted on {{ $user->deleted_at->format('M d, Y H:i') }}.
                        <a href="{{ route('admin.users.restore', $user->id) }}" 
                           class="text-yellow-800 underline hover:text-yellow-900"
                           onclick="return confirm('Restore this user?')">Restore user</a>
                    </p>
                </div>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-container">
                    Save Changes
                </button>
                <a href="{{ route('admin.users.show', $user->id) }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
