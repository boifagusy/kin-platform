@extends('layouts.admin')

@section('content')
<div class="p-8">
    <h1 class="text-2xl font-bold mb-4">Sidebar Diagnostic</h1>
    
    <div class="bg-white rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold mb-2">Kin Alerts Link HTML:</h2>
        <pre class="bg-gray-100 p-4 rounded-lg overflow-auto">
{!! htmlspecialchars('
' . $sidebarHtml . '
') !!}
        </pre>
    </div>
    
    <div class="bg-white rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-2">Route URL:</h2>
        <code class="bg-gray-100 p-2 rounded">{{ route('admin.alerts.index') }}</code>
    </div>
</div>
@endsection
