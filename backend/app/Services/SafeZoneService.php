<?php

namespace App\Services;

use App\Models\SafeZone;
use App\Models\User;
use App\Presenters\SafeZonePresenter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SafeZoneService
{
    public function __construct(
        private SafeZonePresenter $presenter
    ) {}

    public function listForUser(User $user): Collection
    {
        return SafeZone::forUser($user)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function findForUser(User $user, int $id): SafeZone
    {
        return SafeZone::forUser($user)->findOrFail($id);
    }

    public function create(User $user, array $data): SafeZone
    {
        $zone = SafeZone::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_default' => $data['is_default'] ?? false,
        ]);

        if ($zone->is_default) {
            $this->clearOtherDefaults($user, $zone);
        }

        Log::info('SafeZone created', ['user_id' => $user->id, 'zone_id' => $zone->id]);

        return $zone;
    }

    public function update(SafeZone $zone, array $data): SafeZone
    {
        $zone->update($data);

        if ($zone->is_default) {
            $this->clearOtherDefaults($zone->user, $zone);
        }

        Log::info('SafeZone updated', ['zone_id' => $zone->id]);

        return $zone->fresh();
    }

    public function delete(SafeZone $zone): void
    {
        $zone->delete();

        Log::info('SafeZone deleted', ['zone_id' => $zone->id]);
    }

    public function activate(SafeZone $zone): SafeZone
    {
        $this->clearOtherDefaults($zone->user, $zone);

        $zone->update(['is_default' => true]);

        Log::info('SafeZone default changed', ['zone_id' => $zone->id]);

        return $zone->fresh();
    }

    public function deactivate(SafeZone $zone): SafeZone
    {
        $zone->update(['is_default' => false]);

        return $zone->fresh();
    }

    public function dashboardData(User $user): array
    {
        $zones = $this->listForUser($user);
        return $this->presenter->dashboardData($zones);
    }

    private function clearOtherDefaults(User $user, SafeZone $except): void
    {
        SafeZone::forUser($user)
            ->where('id', '!=', $except->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
