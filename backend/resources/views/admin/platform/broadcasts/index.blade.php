@extends('layouts.admin')
@section('title', 'Emergency Broadcasts')
@section('content')
@include('admin.platform.partials.header', [
    'title' => 'Emergency Broadcasts',
    'breadcrumb' => 'Broadcasts',
    'description' => 'Create and manage emergency broadcast alerts.',
    'actions' => [
        ['label' => '+ New Broadcast', 'route' => route('admin.broadcasts.create'), 'icon' => 'warning', 'class' => 'bg-red-500 text-white'],
    ]
])
@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Severity</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 hidden md:table-cell">Audience</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($broadcasts as $b)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $b->title }}</td>
                <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $b->severity === 'critical' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $b->severity }}</span></td>
                <td class="px-4 py-3">{{ $b->status }}</td>
                <td class="px-4 py-3 hidden md:table-cell">{{ $b->audience?->name ?? 'All Users' }}</td>
                <td class="px-4 py-3"><a href="{{ route('admin.broadcasts.edit', $b) }}" class="text-blue-600 hover:underline text-xs">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $broadcasts->links() }}</div>
@endsection
