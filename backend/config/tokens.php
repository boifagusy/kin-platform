<?php

return [
    'invitation' => [
        'length' => 40,                    // Raw token length
        'hash_algo' => 'sha256',           // Always use SHA256
        'expiry_days' => 7,                // Configurable expiry
    ],
];
