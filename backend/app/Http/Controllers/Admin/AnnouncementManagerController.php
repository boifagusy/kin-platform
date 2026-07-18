<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;

class AnnouncementManagerController extends Controller
{
    public function __construct(private AnnouncementService $service) {}

    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.platform.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.platform.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,critical',
            'priority' => 'required|in:low,normal,high,critical',
            'status' => 'required|in:draft,published,scheduled',
            'target_platform' => 'required|in:all,android,ios,web',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'dismissible' => 'boolean',
        ]);

        $this->service->create($data);
        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.platform.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,critical',
            'priority' => 'required|in:low,normal,high,critical',
            'status' => 'required|in:draft,published,scheduled,expired',
            'target_platform' => 'required|in:all,android,ios,web',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'dismissible' => 'boolean',
        ]);

        $this->service->update($announcement, $data);
        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->service->delete($announcement);
        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }
}
