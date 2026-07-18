<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyBroadcast;
use App\Models\Audience;
use App\Services\EmergencyBroadcastService;
use Illuminate\Http\Request;

class BroadcastManagerController extends Controller
{
    public function __construct(private EmergencyBroadcastService $service) {}

    public function index()
    {
        $broadcasts = EmergencyBroadcast::with('audience')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.platform.broadcasts.index', compact('broadcasts'));
    }

    public function create()
    {
        $audiences = Audience::active()->get();
        return view('admin.platform.broadcasts.create', compact('audiences'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'audience_id' => 'nullable|exists:audiences,id',
            'expires_at' => 'nullable|date',
            'status' => 'required|in:draft,active',
        ]);

        $broadcast = $this->service->create($data);
        if ($request->status === 'active') {
            $this->service->activate($broadcast);
        }
        return redirect()->route('admin.broadcasts.index')->with('success', 'Broadcast created.');
    }

    public function edit(EmergencyBroadcast $broadcast)
    {
        $audiences = Audience::active()->get();
        return view('admin.platform.broadcasts.edit', compact('broadcast', 'audiences'));
    }

    public function update(Request $request, EmergencyBroadcast $broadcast)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'audience_id' => 'nullable|exists:audiences,id',
            'expires_at' => 'nullable|date',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        $this->service->update($broadcast, $data);
        return redirect()->route('admin.broadcasts.index')->with('success', 'Broadcast updated.');
    }

    public function destroy(EmergencyBroadcast $broadcast)
    {
        $this->service->cancel($broadcast);
        return redirect()->route('admin.broadcasts.index')->with('success', 'Broadcast cancelled.');
    }
}
