<?php

return [
    'version' => '1.0',

    'groups' => [
        'EMERGENCY' => ['priority' => 4, 'order' => 1],
        'CONTACTS' => ['priority' => 3, 'order' => 2],
        'REMINDERS' => ['priority' => 2, 'order' => 3],
        'ANNOUNCEMENTS' => ['priority' => 1, 'order' => 4],
        'SYSTEM' => ['priority' => 1, 'order' => 5],
    ],

    'definitions' => [
        'trusted_contact_invitation' => [
            'trigger' => 'SERVER_EVENT',
            'category' => 'CONTACTS',
            'type' => 'ACTION_REQUIRED',
            'priority' => 3,
            'storage_policy' => 'SERVER_WITH_LOCAL_CACHE',
            'ttl' => 'UNTIL_ACTION',
            'capabilities' => ['cache', 'sync', 'actions', 'push', 'badge', 'history'],
            'ui' => ['icon' => 'person_add', 'color' => 'primary'],
            'lifecycle' => ['CREATED', 'DELIVERED', 'DISPLAYED', 'ACTIONED', 'ARCHIVED'],
            'actions' => ['ACCEPT', 'DECLINE'],
        ],
        'sos_triggered' => [
            'trigger' => 'SERVER_EVENT',
            'category' => 'EMERGENCY',
            'type' => 'CRITICAL',
            'priority' => 4,
            'storage_policy' => 'SERVER_WITH_LOCAL_CACHE',
            'ttl' => '24h',
            'capabilities' => ['cache', 'sync', 'actions', 'push', 'popup', 'badge', 'history'],
            'ui' => ['icon' => 'emergency', 'color' => 'danger'],
            'lifecycle' => ['CREATED', 'DELIVERED', 'DISPLAYED', 'READ', 'RESPONDING', 'RESOLVED', 'ARCHIVED'],
            'actions' => ['RESPOND', 'RESOLVE'],
        ],
    ],

    'dashboard_order' => ['CRITICAL', 'ACTION_REQUIRED', 'REMINDER', 'WARNING', 'INFO'],

    'storage_policies' => [
        'SERVER_ONLY' => 'No local storage',
        'LOCAL_ONLY' => 'Device only',
        'LOCAL_THEN_SYNC' => 'Created locally, synced when online',
        'SERVER_WITH_LOCAL_CACHE' => 'Server primary, cached locally',
    ],
];
