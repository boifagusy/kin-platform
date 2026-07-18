@extends('layouts.admin')
@section('title', 'Edit Campaign')
@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Campaign</h1>
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif
    <form method="POST" action="{{ route('admin.campaigns.update', $campaign) }}" class="space-y-4 bg-white rounded-xl p-6 shadow-sm border">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" required value="{{ old('title', $campaign->title) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Message Body</label>
            <textarea name="body" required rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('body', $campaign->body) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Audience</label>
            <select name="audience_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">All Users</option>
                @foreach($audiences as $a)
                <option value="{{ $a->id }}" {{ old('audience_id', $campaign->audience_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(['draft','scheduled','sending','sent'] as $s)
                    <option value="{{ $s }}" {{ old('status', $campaign->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Schedule</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <button type="submit" class="px-6 py-2 bg-[#1A5632] text-white rounded-lg text-sm">Update Campaign</button>
    </form>
</div>
@endsection
