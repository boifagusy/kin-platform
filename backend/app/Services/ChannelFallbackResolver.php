<?php

namespace App\Services;

use App\Models\UserPreference;

class ChannelFallbackResolver
{
    private array $priorityOrder = ['push', 'sms', 'email', 'whatsapp'];

    public function __construct(?array $priorityOrder = null)
    {
        if ($priorityOrder) {
            $this->priorityOrder = $priorityOrder;
        }
    }

    public function resolve(string $preferredChannel, int $userId): ?string
    {
        $preferences = UserPreference::getPreferences($userId);
        $channels = $preferences['channels'] ?? [];

        // Start with preferred channel
        $orderedChannels = array_unique(
            array_merge([$preferredChannel], $this->priorityOrder)
        );

        foreach ($orderedChannels as $channel) {
            if ($this->isChannelAvailable($channel, $channels)) {
                return $channel;
            }
        }

        return null; // No available channel
    }

    public function getFallbackChain(string $preferredChannel, int $userId): array
    {
        $preferences = UserPreference::getPreferences($userId);
        $channels = $preferences['channels'] ?? [];

        $orderedChannels = array_unique(
            array_merge([$preferredChannel], $this->priorityOrder)
        );

        return array_values(array_filter(
            $orderedChannels,
            fn($ch) => $this->isChannelAvailable($ch, $channels)
        ));
    }

    private function isChannelAvailable(string $channel, array $preferences): bool
    {
        return $preferences[$channel] ?? true; // Default to enabled
    }
}
