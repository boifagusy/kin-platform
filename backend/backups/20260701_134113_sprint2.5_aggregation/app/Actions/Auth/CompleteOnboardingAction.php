<?php

namespace App\Actions\Auth;

use App\Models\User;

class CompleteOnboardingAction
{
    public function execute(string $phone, ?string $checkInTime = null, int $gracePeriodMinutes = 15, ?array $trustedContact = null): array
    {
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }
        
        // Mark onboarding as completed
        $user->onboarding_completed = true;
        $user->last_checkin_at = null; // Will be set when first check-in happens
        $user->save();
        
        // TODO: Save check-in settings and trusted contact to separate tables
        
        return [
            'success' => true,
            'message' => 'Onboarding completed successfully',
            'user_id' => $user->id
        ];
    }
}
