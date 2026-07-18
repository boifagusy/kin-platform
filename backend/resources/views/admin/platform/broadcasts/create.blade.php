@extends('layouts.admin')
@section('title', 'Create Emergency Broadcast')
@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Emergency Broadcast</h1>
    <form method="POST" action="{{ route('admin.broadcasts.store') }}" class="space-y-4 bg-white rounded-xl p-6 shadow-sm border">
        @csrf
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif
        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Message</label>
            <textarea name="message" required rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Severity</label>
                <select name="severity" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="draft">Draft</option>
                    <option value="active">Active Now</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Audience</label>
            <select name="audience_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">All Users</option>
                @foreach($audiences as $a)
                <option value="{{ $a->id }}">{{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-6 py-2 bg-red-500 text-white rounded-lg text-sm">Create Broadcast</button>
    </form>
</div>
@endsection
