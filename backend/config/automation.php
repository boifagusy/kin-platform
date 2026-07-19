<?php

return [
    'rules' => [

        'sos_triggered' => [
            [
                'name' => 'SOS Alert Notification',
                'category' => 'security',
                'channel' => 'push',
                'conditions' => [],
                'title_template' => '🚨 SOS Alert',
                'body_template' => 'Emergency alert triggered. Type: {{incident_type}}',
            ],
            [
                'name' => 'SOS SMS Backup',
                'category' => 'security',
                'channel' => 'sms',
                'conditions' => [],
                'title_template' => 'SOS Alert',
                'body_template' => 'Emergency alert triggered. Please check on the user.',
            ],
        ],

        'checkin_completed' => [
            [
                'name' => 'Check-in Confirmation',
                'category' => 'general',
                'channel' => 'push',
                'conditions' => [],
                'title_template' => '✅ Check-in Complete',
                'body_template' => 'Your check-in has been recorded successfully.',
            ],
        ],

    ],
];
