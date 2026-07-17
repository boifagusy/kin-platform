<?php
namespace App\Services;
use App\Models\Audience;

class AudienceService
{
    public function create(array $data): Audience
    {
        return Audience::create($data);
    }

    public function update(Audience $audience, array $data): Audience
    {
        $audience->update($data);
        return $audience;
    }

    public function delete(Audience $audience): void
    {
        $audience->delete();
    }

    public function getMatchingAudiences(string $platform, ?int $versionCode = null): array
    {
        return Audience::active()
            ->forPlatform($platform)
            ->get()
            ->filter(fn($a) => $a->matchesClient($platform, $versionCode))
            ->values()
            ->toArray();
    }
}
