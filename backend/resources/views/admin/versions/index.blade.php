@extends('layouts.admin')

@section('title', 'Version Manager')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Version Manager</h1>
        <a href="{{ route('admin.versions.create') }}" class="px-4 py-2 bg-[#1A5632] text-white rounded-lg text-sm">Create Version</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-gray-500">
                    <th class="px-5 py-3">Code</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Release Date</th>
                    <th class="px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($versions as $version)
                    <tr class="border-t {{ $version->trashed() ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3 font-mono">{{ $version->version_code }}</td>
                        <td class="px-5 py-3">{{ $version->version_name }}</td>
                        <td class="px-5 py-3">
                            @if($version->trashed())
                                <span class="text-xs text-gray-400">Deleted</span>
                            @elseif($version->is_active)
                                <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Active</span>
                            @else
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $version->status ?? 'Inactive' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $version->release_date?->format('M d, Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                @if(!$version->trashed())
                                    <a href="{{ route('admin.versions.edit', $version) }}" class="text-xs text-[#1A5632]">Edit</a>
                                    @if(!$version->is_active)
                                        <form method="POST" action="{{ route('admin.versions.activate', $version) }}" class="inline">
                                            @csrf
                                            <button class="text-xs text-blue-600">Activate</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.versions.channels', $version) }}" class="text-xs text-gray-500">Channels</a>
                                    <a href="{{ route('admin.versions.policies', $version) }}" class="text-xs text-gray-500">Policies</a>
                                    <form method="POST" action="{{ route('admin.versions.destroy', $version) }}" class="inline" onsubmit="return confirm('Delete this version?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-500">Delete</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.versions.restore', $version->id) }}" class="inline">
                                        @csrf
                                        <button class="text-xs text-green-600">Restore</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No versions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
