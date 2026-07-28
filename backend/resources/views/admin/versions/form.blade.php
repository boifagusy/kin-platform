@extends('layouts.admin')

@section('title', $version ? 'Edit Version' : 'Create Version')

    <a href="{{ route('admin.versions.index') }}" class="text-sm text-gray-500 mb-4 inline-block">← Back to Versions</a>
@section('content')
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $version ? 'Edit Version' : 'Create Version' }}</h1>

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ $version ? route('admin.versions.update', $version) : route('admin.versions.store') }}" class="bg-white rounded-xl p-6 shadow-sm border space-y-4">
        @csrf
        @if($version) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium mb-1">Version Code</label>
            <input type="number" name="version_code" value="{{ old('version_code', $version->version_code ?? '') }}" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Version Name</label>
            <input type="text" name="version_name" value="{{ old('version_name', $version->version_name ?? '') }}" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Release Notes</label>
            <textarea name="release_notes" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('release_notes', $version->release_notes ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Minimum Version Code</label>
            <input type="number" name="min_version_code" value="{{ old('min_version_code', $version->min_version_code ?? '') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <button type="submit" class="px-6 py-2 bg-[#1A5632] text-white rounded-lg text-sm">{{ $version ? 'Update' : 'Create' }}</button>
        <a href="{{ route('admin.versions.index') }}" class="ml-2 text-sm text-gray-500">Cancel</a>
    </form>
</div>
@endsection
