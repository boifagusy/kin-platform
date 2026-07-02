<?php

return [
    'score_weights' => [
        'missed_checkin' => 20,
        'missed_checkin_long' => 30,
        'sos_unacknowledged' => 40,
        'low_battery' => 10,
        'offline_long' => 15,
        'guardian_unresponsive' => 15,
    ],
    'decay_rate' => 5, // points per hour
    'history_days' => 7,
    'cache_ttl' => 300, // seconds
];
