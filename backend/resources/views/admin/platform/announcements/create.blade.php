@extends('layouts.admin')
@section('title', 'Create Announcement')
@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Announcement</h1>
    <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-4 bg-white rounded-xl p-6 shadow-sm border">
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
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="info">Info</option>
                    <option value="success">Success</option>
                    <option value="warning">Warning</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select name="priority" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="scheduled">Scheduled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Platform</label>
                <select name="target_platform" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="all">All</option>
                    <option value="android">Android</option>
                    <option value="ios">iOS</option>
                    <option value="web">Web</option>
                </select>
            </div>
        </div>
        <button type="submit" class="px-6 py-2 bg-[#1A5632] text-white rounded-lg text-sm">Create Announcement</button>
    </form>
</div>
@endsection
