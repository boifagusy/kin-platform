@extends('layouts.admin')
@section('title', 'Edit Announcement')
@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Announcement</h1>
    
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" class="space-y-4 bg-white rounded-xl p-6 shadow-sm border">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" required value="{{ old('title', $announcement->title) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Message</label>
            <textarea name="message" required rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('message', $announcement->message) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(['info','success','warning','critical'] as $t)
                    <option value="{{ $t }}" {{ old('type', $announcement->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select name="priority" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(['low','normal','high','critical'] as $p)
                    <option value="{{ $p }}" {{ old('priority', $announcement->priority) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(['draft','published','scheduled','expired'] as $s)
                    <option value="{{ $s }}" {{ old('status', $announcement->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Platform</label>
                <select name="target_platform" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(['all','android','ios','web'] as $p)
                    <option value="{{ $p }}" {{ old('target_platform', $announcement->target_platform) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="px-6 py-2 bg-[#1A5632] text-white rounded-lg text-sm">Update Announcement</button>
    </form>
</div>
@endsection
