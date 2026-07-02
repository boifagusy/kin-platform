<?php

namespace App\Services\Watchtower;

class NotificationRouterService
{
    private $routing = [
        'infrastructure' => ['devops', 'sre'],
        'database' => ['backend', 'dba'],
        'plugin' => ['android', 'ios'],
        'safety' => ['operations', 'support'],
        'security' => ['security', 'compliance'],
        'default' => ['admin'],
    ];

    public function route(string $alertType, array $data): array
    {
        $role = $this->routing[$alertType] ?? $this->routing['default'];
        return ['roles' => $role, 'data' => $data];
    }

    public function getChannels(): array
    {
        return ['email', 'push', 'log', 'telegram', 'slack'];
    }
}
