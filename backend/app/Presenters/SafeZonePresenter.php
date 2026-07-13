<?php
namespace App\Presenters;
use App\Models\SafeZone;
use Illuminate\Support\Collection;

class SafeZonePresenter
{
    public function present(SafeZone $zone): array
    {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'address' => $zone->address,
            'latitude' => (float) $zone->latitude,
            'longitude' => (float) $zone->longitude,
            'is_default' => $zone->is_default,
            'created_at' => $zone->created_at?->toISOString(),
            'updated_at' => $zone->updated_at?->toISOString(),
        ];
    }

    public function collection(Collection $zones): array
    {
        return $zones->map(fn($zone) => $this->present($zone))->values()->toArray();
    }

    public function dashboardData(Collection $zones): array
    {
        $default = $zones->firstWhere('is_default', true);
        return [
            'count' => $zones->count(),
            'default_zone' => $default?->name,
            'current_zone' => null,
            'inside_safe_zone' => false,
            'zones' => $this->collection($zones),
        ];
    }
}
