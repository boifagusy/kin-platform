<?php

namespace App\Actions\Dashboard;

use App\Models\User;
use App\Models\TrustedContact;

class GetDashboardAction
{
    public function execute(string $phone): array
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found',
            ];
        }

        // Get trusted contact (only one for MVP) — using user_id
        $trustedContact = TrustedContact::where('user_id', $user->id)
            ->where('active', 1)
            ->first();

        // Determine invite_status
        $inviteStatus = 'required';
        $contactData = null;

        if ($trustedContact) {
            $contactData = [
                'name' => $trustedContact->name,
                'phone' => $trustedContact->phone,
            ];

            // Use verified field as invite_status
            if ($trustedContact->verified) {
                $inviteStatus = 'accepted';
            } else {
                $inviteStatus = 'waiting';
            }
        }

        // Count trusted contacts
        $trustedContactsCount = TrustedContact::where('user_id', $user->id)
            ->where('active', 1)
            ->count();

        return [
            'success' => true,

            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'email' => $user->email,
                'email' => $user->email,
                'status' => 'Connected',
                'sms_available' => true,
            ],

            'trusted_contact' => $contactData,
            'invite_status' => $inviteStatus,

            'safety' => [
                'status' => 'Protected',
                'streak' => 7,
            ],

            'checkin' => [
                'next_time' => '9:00 PM',
                'last_checkin' => '2 hours ago',
            ],

            'contacts' => [
                'count' => $trustedContactsCount,
            ],

            'reminder' => [
                'title' => 'Reminder in 2 hours',
                'button_text' => 'Check In Now',
            ],

            'tasks' => [
                [
                    'id' => 'location',
                    'title' => 'Enable Location',
                    'description' => 'Turn on location for emergency alerts',
                    'completed' => false,
                ],
                [
                    'id' => 'home_zone',
                    'title' => 'Add Home Zone',
                    'description' => 'Set your home location for automatic check-ins',
                    'completed' => false,
                ],
                [
                    'id' => 'duress_pin',
                    'title' => 'Create Duress PIN',
                    'description' => 'Set up a silent alert PIN',
                    'completed' => false,
                ],
            ],
        ];
    }
}
