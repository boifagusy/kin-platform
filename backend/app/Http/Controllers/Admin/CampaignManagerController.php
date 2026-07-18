<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushCampaign;
use App\Models\Announcement;
use App\Models\Audience;
use App\Services\PushCampaignService;
use Illuminate\Http\Request;

class CampaignManagerController extends Controller
{
    public function __construct(private PushCampaignService $service) {}

    public function index()
    {
        $campaigns = PushCampaign::with('audience')->withCount('deliveries')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.platform.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $audiences = Audience::active()->get();
        return view('admin.platform.campaigns.create', compact('audiences'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'audience_id' => 'nullable|exists:audiences,id',
            'scheduled_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,sending',
        ]);

        $campaign = $this->service->create($data);
        if ($request->status === 'sending') {
            $this->service->send($campaign);
        }
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign created.');
    }

    public function edit(PushCampaign $campaign)
    {
        $audiences = Audience::active()->get();
        return view('admin.platform.campaigns.edit', compact('campaign', 'audiences'));
    }

    public function update(Request $request, PushCampaign $campaign)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'audience_id' => 'nullable|exists:audiences,id',
            'scheduled_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,sending,sent',
        ]);

        $this->service->update($campaign, $data);
        if ($request->status === 'sending') {
            $this->service->send($campaign);
        }
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign updated.');
    }

    public function destroy(PushCampaign $campaign)
    {
        $this->service->delete($campaign);
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign deleted.');
    }
}
