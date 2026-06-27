@extends('layouts.admin')

@section('title', 'Test Suspend')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Test Suspend for User {{ $user->id }}</h1>
    
    <div class="bg-white rounded-xl border p-6 mb-4">
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Current Status:</strong> 
            @if($user->status == 'suspended')
                <span class="text-red-600 font-bold">SUSPENDED</span>
            @else
                <span class="text-green-600 font-bold">ACTIVE</span>
            @endif
        </p>
        <p><strong>deleted_at:</strong> {{ $user->deleted_at ?? 'NULL' }}</p>
    </div>
    
    <div class="flex gap-4">
        <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}" onsubmit="return confirm('Suspend this user?')">
            @csrf
            <input type="hidden" name="reason" value="Test from test page">
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg">Suspend User</button>
        </form>
        
        <form method="POST" action="{{ route('admin.users.activate', $user->id) }}">
            @csrf
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg">Activate User</button>
        </form>
        
        <a href="{{ route('admin.users.show', $user->id) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg">Back to User</a>
    </div>
</div>
@endsection
