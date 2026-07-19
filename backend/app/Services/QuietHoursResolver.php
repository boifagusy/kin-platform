<?php

namespace App\Services;

class QuietHoursResolver
{
    private array $quietHours = [
        'start' => 22, // 10 PM
        'end' => 7,    // 7 AM
    ];

    private array $criticalCategories = ['security', 'emergency', 'sos'];

    public function __construct(?array $quietHours = null)
    {
        if ($quietHours) {
            $this->quietHours = $quietHours;
        }
    }

    public function isQuietTime(string $category = 'general'): bool
    {
        if (in_array($category, $this->criticalCategories)) {
            return false; // Critical notifications bypass quiet hours
        }

        $hour = (int) now()->format('H');

        if ($this->quietHours['start'] > $this->quietHours['end']) {
            // Overnight window: e.g., 22-07
            return $hour >= $this->quietHours['start'] || $hour < $this->quietHours['end'];
        }

        // Same-day window
        return $hour >= $this->quietHours['start'] && $hour < $this->quietHours['end'];
    }

    public function shouldDefer(string $category): bool
    {
        return $this->isQuietTime($category);
    }

    public function getQuietHours(): array
    {
        return $this->quietHours;
    }
}
