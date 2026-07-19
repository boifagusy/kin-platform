@extends('layouts.admin')
@section('title', 'Create Template')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Template</h1>
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif
    <form method="POST" action="{{ route('admin.templates.store') }}" class="space-y-4 bg-white rounded-xl p-6 shadow-sm border">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="system">System</option>
                    <option value="security">Security</option>
                    <option value="marketing">Marketing</option>
                    <option value="transactional">Transactional</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">SMS Content</label>
            <textarea name="sms_content" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Use @{{first_name}} for variables"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">WhatsApp Content</label>
            <textarea name="whatsapp_content" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Push Content</label>
            <textarea name="push_content" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email Content</label>
            <textarea name="email_content" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>
        <button type="submit" class="px-6 py-2 bg-[#1A5632] text-white rounded-lg text-sm">Create Template</button>
    </form>
</div>
@endsection
