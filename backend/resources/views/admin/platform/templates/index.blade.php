@extends('layouts.admin')
@section('title', 'Templates')
@section('content')
@include('admin.platform.partials.header', [
    'title' => 'Templates',
    'breadcrumb' => 'Templates',
    'description' => 'Create and manage reusable message templates.',
    'actions' => [
        ['label' => '+ New Template', 'route' => route('admin.templates.create'), 'icon' => 'add', 'class' => 'bg-[#1A5632] text-white'],
    ]
])
@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3 hidden md:table-cell">Category</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 hidden md:table-cell">Version</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $t)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $t->name }}</td>
                <td class="px-4 py-3 hidden md:table-cell"><span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $t->category }}</span></td>
                <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $t->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $t->status }}</span></td>
                <td class="px-4 py-3 hidden md:table-cell">v{{ $t->version }}</td>
                <td class="px-4 py-3"><a href="{{ route('admin.templates.edit', $t) }}" class="text-blue-600 hover:underline text-xs">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $templates->links() }}</div>
@endsection
