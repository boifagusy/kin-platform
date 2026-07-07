<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPreference;

class UserPreferenceService
{
    private array $defaults = [
        'safety' => [
            'monitoring' => ['type' => 'boolean', 'default' => true],
            'location_tracking' => ['type' => 'boolean', 'default' => true],
            'checkin_interval' => ['type' => 'integer', 'default' => 60],
            'sos_power_button' => ['type' => 'boolean', 'default' => false],
            'duress_pin' => ['type' => 'string', 'default' => null],
            'trust_contacts' => ['type' => 'json', 'default' => []],
            'background_service' => ['type' => 'boolean', 'default' => true],
            'auto_start_boot' => ['type' => 'boolean', 'default' => false],
            'battery_optimization' => ['type' => 'boolean', 'default' => false],
        ],
        'notifications' => [
            'sound' => ['type' => 'boolean', 'default' => true],
            'vibration' => ['type' => 'boolean', 'default' => true],
            'reminders' => ['type' => 'boolean', 'default' => true],
        ],
        'privacy' => [
            'location_precision' => ['type' => 'string', 'default' => 'high'],
            'share_status' => ['type' => 'boolean', 'default' => true],
        ],
        'appearance' => [
            'theme' => ['type' => 'string', 'default' => 'system'],
            'dark_mode' => ['type' => 'boolean', 'default' => false],
        ],
        'account' => [
            'language' => ['type' => 'string', 'default' => 'en'],
            'timezone' => ['type' => 'string', 'default' => 'auto'],
        ],
    ];

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function getDefault(string $category, string $key): mixed
    {
        return $this->defaults[$category][$key]['default'] ?? null;
    }

    public function getDefaultType(string $category, string $key): string
    {
        return $this->defaults[$category][$key]['type'] ?? 'string';
    }

    public function getAll(User $user): array
    {
        $preferences = UserPreference::where('user_id', $user->id)->get();
        $result = [];

        foreach ($this->defaults as $category => $keys) {
            $result[$category] = [];
            foreach ($keys as $key => $config) {
                $pref = $preferences->firstWhere(function ($p) use ($category, $key) {
                    return $p->category === $category && $p->preference_key === $key;
                });
                $result[$category][$key] = $pref ? $pref->typed_value : $config['default'];
            }
        }

        return $result;
    }

    public function get(User $user, string $category, string $key): mixed
    {
        $preference = UserPreference::where('user_id', $user->id)
            ->where('category', $category)
            ->where('preference_key', $key)
            ->first();

        if ($preference) {
            return $preference->typed_value;
        }

        return $this->getDefault($category, $key);
    }

    public function set(User $user, string $category, string $key, mixed $value): void
    {
        $type = $this->getDefaultType($category, $key);
        $storedValue = $this->formatValueForStorage($value, $type);

        UserPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'category' => $category,
                'preference_key' => $key,
            ],
            [
                'value_type' => $type,
                'value' => $storedValue,
            ]
        );
    }

    public function setMany(User $user, string $category, array $preferences): void
    {
        foreach ($preferences as $key => $value) {
            $this->set($user, $category, $key, $value);
        }
    }

    public function reset(User $user, string $category, string $key): void
    {
        UserPreference::where('user_id', $user->id)
            ->where('category', $category)
            ->where('preference_key', $key)
            ->delete();
    }

    public function resetCategory(User $user, string $category): void
    {
        UserPreference::where('user_id', $user->id)
            ->where('category', $category)
            ->delete();
    }

    public function getAllForCategory(User $user, string $category): array
    {
        return $this->getAll($user)[$category] ?? [];
    }

    private function formatValueForStorage(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) (int) $value,
            'string' => (string) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}
