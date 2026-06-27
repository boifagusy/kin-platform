@extends('layouts.admin')

@section('content')
<div class="p-8">
    <h1 class="text-2xl font-bold mb-4">Sidebar Debug</h1>
    
    <div class="bg-white rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold mb-2">Kin Alerts Link HTML:</h2>
        <pre class="bg-gray-100 p-4 rounded-lg overflow-auto" id="linkHtml"></pre>
    </div>
    
    <div class="bg-white rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-2">Test Click:</h2>
        <button onclick="testClick()" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Test Click Kin Alerts</button>
        <div id="result" class="mt-4"></div>
    </div>
</div>

<script>
// Get the actual Kin Alerts link
const links = document.querySelectorAll('a');
let alertLinkHtml = '';
for (let link of links) {
    if (link.textContent.includes('Kin Alerts')) {
        alertLinkHtml = link.outerHTML;
        break;
    }
}
document.getElementById('linkHtml').innerText = alertLinkHtml;

function testClick() {
    // Find and click the Kin Alerts link programmatically
    for (let link of links) {
        if (link.textContent.includes('Kin Alerts')) {
            document.getElementById('result').innerHTML = 'Found link with href: ' + link.href + '<br>Attempting to click...';
            link.click();
            break;
        }
    }
}
</script>
@endsection
