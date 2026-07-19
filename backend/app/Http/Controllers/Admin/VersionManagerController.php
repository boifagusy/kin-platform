<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Version;
use App\Models\UpdatePolicy;
use App\Models\VersionChannel;
use App\Services\VersionService;
use App\Services\UpdatePolicyService;
use Illuminate\Http\Request;

class VersionManagerController extends Controller
{
    public function __construct(
        private VersionService $versionService,
        private UpdatePolicyService $policyService,
    ) {}

    // Versions
    public function index()
    {
        return view('admin.versions.index', [
            'versions' => $this->versionService->getAllWithTrashed(),
        ]);
    }

    public function create()
    {
        return view('admin.versions.form', ['version' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'version_code' => 'required|integer|min:1',
            'version_name' => 'required|string|max:50',
            'release_notes' => 'nullable|string',
            'min_version_code' => 'nullable|integer|min:1',
        ]);

        $this->versionService->create($data);
        return redirect()->route('admin.versions.index')->with('success', 'Version created.');
    }

    public function edit(Version $version)
    {
        return view('admin.versions.form', ['version' => $version]);
    }

    public function update(Request $request, Version $version)
    {
        $data = $request->validate([
            'version_code' => 'required|integer|min:1',
            'version_name' => 'required|string|max:50',
            'release_notes' => 'nullable|string',
            'min_version_code' => 'nullable|integer|min:1',
        ]);

        $this->versionService->update($version, $data);
        return redirect()->route('admin.versions.index')->with('success', 'Version updated.');
    }

    public function destroy(Version $version)
    {
        $this->versionService->delete($version);
        return back()->with('success', 'Version deleted (soft).');
    }

    public function activate(Version $version)
    {
        $this->versionService->activate($version->id);
        return back()->with('success', 'Version activated.');
    }

    public function restore(int $id)
    {
        $version = $this->versionService->restore($id);
        $this->versionService->activate($version->id);
        return back()->with('success', 'Version restored and activated.');
    }

    // Channels
    public function channels(Version $version)
    {
        return view('admin.versions.channels', [
            'version' => $version,
            'channels' => $version->channels,
        ]);
    }

    public function storeChannel(Request $request, Version $version)
    {
        $data = $request->validate([
            'platform' => 'required|string',
            'channel' => 'required|string',
            'download_url' => 'required|url',
        ]);
        $data['enabled'] = true;

        $this->versionService->addChannel($version, $data);
        return back()->with('success', 'Channel added.');
    }

    public function destroyChannel(VersionChannel $channel)
    {
        $this->versionService->removeChannel($channel);
        return back()->with('success', 'Channel removed.');
    }

    // Policies
    public function policies(Version $version)
    {
        return view('admin.versions.policies', [
            'version' => $version,
            'policies' => $version->policies ?? UpdatePolicy::where('version_id', $version->id)->get(),
        ]);
    }

    public function storePolicy(Request $request, Version $version)
    {
        $data = $request->validate([
            'platform' => 'required|string',
            'policy' => 'required|string',
            'priority' => 'required|integer|min:1',
            'grace_days' => 'nullable|integer|min:0',
            'reason' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);
        $data['version_id'] = $version->id;
        $data['is_active'] = true;

        $this->policyService->create($data);
        return back()->with('success', 'Policy created.');
    }

    public function updatePolicy(Request $request, UpdatePolicy $policy)
    {
        $data = $request->validate([
            'platform' => 'required|string',
            'policy' => 'required|string',
            'priority' => 'required|integer|min:1',
            'grace_days' => 'nullable|integer|min:0',
            'reason' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        $this->policyService->update($policy, $data);
        return back()->with('success', 'Policy updated.');
    }

    public function destroyPolicy(UpdatePolicy $policy)
    {
        $this->policyService->delete($policy);
        return back()->with('success', 'Policy deleted.');
    }

    // Analytics
    public function analytics()
    {
        $versions = Version::withTrashed()->get();
        $activeCount = $versions->where('is_active', true)->count();
        $policies = UpdatePolicy::where('is_active', true)->get();
        $scheduledCount = $policies->where('starts_at', '>', now())->count();
        $expiredCount = $policies->where('expires_at', '<=', now())->count();

        return view('admin.versions.analytics', [
            'total_versions' => $versions->count(),
            'active_versions' => $activeCount,
            'soft_deleted' => $versions->whereNotNull('deleted_at')->count(),
            'total_policies' => $policies->count(),
            'scheduled_releases' => $scheduledCount,
            'expired_releases' => $expiredCount,
        ]);
    }
}
