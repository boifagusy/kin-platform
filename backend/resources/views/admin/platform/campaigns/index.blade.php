@extends('layouts.admin')
@section('title', 'Campaigns')
@section('content')
@include('admin.platform.partials.header', [
    'title' => 'Push Campaigns',
    'breadcrumb' => 'Campaigns',
    'description' => 'Create and manage push notification campaigns.',
    'actions' => [
        ['label' => '+ New Campaign', 'route' => route('admin.campaigns.create'), 'icon' => 'add', 'class' => 'bg-[#1A5632] text-white'],
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
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 hidden md:table-cell">Audience</th>
                <th class="px-4 py-3">Deliveries</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($campaigns as $c)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $c->title }}</td>
                <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $c->status === 'sent' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $c->status }}</span></td>
                <td class="px-4 py-3 hidden md:table-cell">{{ $c->audience?->name ?? 'All Users' }}</td>
                <td class="px-4 py-3">{{ $c->deliveries_count }}</td>
                <td class="px-4 py-3"><a href="{{ route('admin.campaigns.edit', $c) }}" class="text-blue-600 hover:underline text-xs">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $campaigns->links() }}</div>
@endsection
