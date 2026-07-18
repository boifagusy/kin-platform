@extends('layouts.admin')
@section('title', 'Announcements')

@section('content')
@include('admin.platform.partials.header', [
    'title' => 'Announcements',
    'breadcrumb' => 'Announcements',
    'description' => 'Create, manage and publish platform announcements.',
    'actions' => [
        ['label' => '+ New Announcement', 'route' => route('admin.announcements.create'), 'icon' => 'add', 'class' => 'bg-[#1A5632] text-white'],
    ]
])

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left hidden sm:table-header-group">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3 hidden md:table-cell">Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 hidden lg:table-cell">Priority</th>
                <th class="px-4 py-3 hidden lg:table-cell">Platform</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($announcements as $a)
            <tr class="border-t">
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $a->title }}</p>
                    <p class="text-xs text-gray-500 sm:hidden">{{ $a->type }} · {{ $a->status }}</p>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $a->type }}</span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $a->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $a->status }}</span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">{{ $a->priority }}</td>
                <td class="px-4 py-3 hidden lg:table-cell">{{ $a->target_platform }}</td>
                <td class="px-4 py-3">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.announcements.edit', $a) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                        <form action="{{ route('admin.announcements.destroy', $a) }}" method="POST" onsubmit="return confirm('Delete?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-xs">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $announcements->links() }}</div>
@endsection
