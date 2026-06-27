@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Test Escalate</h1>
    <button onclick="escalate()" class="bg-orange-600 text-white px-4 py-2 rounded-lg">Escalate Alert #7</button>
    <div id="result" class="mt-4"></div>
</div>

<script>
function escalate() {
    fetch('/admin/test-escalate/7', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        if (data.success) {
            alert('Escalated to: ' + data.new_priority);
        } else {
            alert('Failed: ' + data.message);
        }
    });
}
</script>
@endsection
