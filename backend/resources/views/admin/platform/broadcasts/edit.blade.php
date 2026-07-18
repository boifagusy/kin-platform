@extends('layouts.admin')
@section('title', 'Edit Emergency Broadcast')
@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Emergency Broadcast</h1>
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif
    <form method="POST" action="{{ route('admin.broadcasts.update', $broadcast) }}" class="space-y-4 bg-white rounded-xl p-6 shadow-sm border">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" required value="{{ old('title', $broadcast->title) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Message</label>
            <textarea name="message" required rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('message', $broadcast->message) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Severity</label>
                <select name="severity" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(['low','medium','high','critical'] as $s)
                    <option value="{{ $s }}" {{ old('severity', $broadcast->severity) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(['draft','active','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ old('status', $broadcast->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Audience</label>
            <select name="audience_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">All Users</option>
                @foreach($audiences as $a)
                <option value="{{ $a->id }}" {{ old('audience_id', $broadcast->audience_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-6 py-2 bg-red-500 text-white rounded-lg text-sm">Update Broadcast</button>
    </form>
</div>
@endsection
