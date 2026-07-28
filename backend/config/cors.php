<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'http://10.49.170.3:5173',
        'http://10.49.170.3:5174',
        '*',
    ],
    'allowed_origins_patterns' => [
        '#^http://localhost:\d+$#',
        '#^http://127\.0\.0\.1:\d+$#',
        '#^http://10\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+$#',
        '#^http://100\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
