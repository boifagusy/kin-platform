@extends('layouts.admin')

@section('content')
<div class="p-8">
    <h1 class="text-2xl font-bold mb-4">Click Test</h1>
    
    <div class="space-y-4">
        <div>
            <button onclick="testClick()" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Test Alert Link</button>
            <div id="result" class="mt-2"></div>
        </div>
        
        <div class="bg-yellow-100 p-4 rounded-lg">
            <p><strong>Instructions:</strong></p>
            <ol class="list-decimal ml-4 mt-2">
                <li>Click the "Test Alert Link" button above</li>
                <li>Check if you're redirected to /admin/alerts</li>
                <li>Open browser console (F12) and check for errors</li>
            </ol>
        </div>
        
        <div class="bg-gray-100 p-4 rounded-lg">
            <p><strong>Debug Info:</strong></p>
            <pre id="debug" class="mt-2 text-sm"></pre>
        </div>
    </div>
</div>

<script>
function testClick() {
    let url = '/admin/alerts';
    document.getElementById('debug').innerText = 'Attempting to go to: ' + url + '\n' + new Date().toString();
    
    try {
        window.location.href = url;
    } catch(e) {
        document.getElementById('debug').innerText += '\nError: ' + e.message;
    }
}
</script>
@endsection
