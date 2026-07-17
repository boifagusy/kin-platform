<?php
namespace App\Services;
use App\Models\Announcement;

class AnnouncementService
{
    public function create(array $data): Announcement
    {
        return Announcement::create($data);
    }

    public function update(Announcement $announcement, array $data): Announcement
    {
        $announcement->update($data);
        return $announcement;
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }

    public function publish(Announcement $announcement): Announcement
    {
        $announcement->update(['status' => 'published']);
        return $announcement;
    }

    public function unpublish(Announcement $announcement): Announcement
    {
        $announcement->update(['status' => 'draft']);
        return $announcement;
    }

    public function getActive(string $platform, ?string $version = null): array
    {
        $query = Announcement::active()->forPlatform($platform);

        if ($version) {
            $query->where(function ($q) use ($version) {
                $q->whereNull('min_version')->orWhere('min_version', '<=', $version);
            })->where(function ($q) use ($version) {
                $q->whereNull('max_version')->orWhere('max_version', '>=', $version);
            });
        }

        return $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
