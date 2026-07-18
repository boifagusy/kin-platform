<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $table = 'user_preferences';

    protected $fillable = ['user_id', 'category', 'key', 'value', 'value_type'];

    const CATEGORY_NOTIFICATIONS = 'notifications';

    const VALID_KEYS = [
        'channels.sms',
        'channels.email',
        'channels.whatsapp',
        'channels.push',
        'categories.security',
        'categories.marketing',
        'categories.system',
    ];

    const DEFAULTS = [
        'channels.sms' => true,
        'channels.email' => true,
        'channels.whatsapp' => true,
        'channels.push' => true,
        'categories.security' => true,
        'categories.marketing' => true,
        'categories.system' => true,
    ];

    public static function getPreferences(int $userId): array
    {
        $stored = self::where('user_id', $userId)
            ->where('category', self::CATEGORY_NOTIFICATIONS)
            ->pluck('value', 'key')
            ->toArray();

        $preferences = [];
        foreach (self::DEFAULTS as $key => $default) {
            $preferences[$key] = isset($stored[$key])
                ? ($stored[$key] === 'true' || $stored[$key] === '1')
                : $default;
        }

        return self::formatPreferences($preferences);
    }

    public static function setPreferences(int $userId, array $input): void
    {
        $flattened = self::flattenInput($input);

        \DB::transaction(function () use ($userId, $flattened) {
            foreach ($flattened as $key => $value) {
                if (!in_array($key, self::VALID_KEYS)) {
                    continue;
                }

                $boolValue = $value ? 'true' : 'false';

                self::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'category' => self::CATEGORY_NOTIFICATIONS,
                        'key' => $key,
                    ],
                    [
                        'value' => $boolValue,
                        'value_type' => 'boolean',
                    ]
                );
            }
        });
    }

    private static function flattenInput(array $input): array
    {
        $flattened = [];
        foreach (['channels', 'categories'] as $group) {
            if (isset($input[$group]) && is_array($input[$group])) {
                foreach ($input[$group] as $name => $value) {
                    $flattened["{$group}.{$name}"] = $value;
                }
            }
        }
        return $flattened;
    }

    private static function formatPreferences(array $flat): array
    {
        $result = ['channels' => [], 'categories' => []];
        foreach ($flat as $key => $value) {
            $parts = explode('.', $key);
            if (count($parts) === 2) {
                $result[$parts[0]][$parts[1]] = $value;
            }
        }
        return $result;
    }
}
