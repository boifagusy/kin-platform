<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserActionService
{
    public function suspendUser(User $user, string $reason, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($user, $reason, $notes) {
            // Update the users table status column
            $user->status = 'suspended';
            $user->save();
            
            // Create status record
            UserStatus::create([
                'user_id' => $user->id,
                'status' => 'suspended',
                'reason' => $reason,
                'notes' => $notes,
                'suspended_by' => Auth::guard('admin')->id(),
                'suspended_at' => now(),
            ]);
            
            // Log to audit center
            AdminLog::create([
                'admin_user_id' => Auth::guard('admin')->id(),
                'action' => 'USER_SUSPENDED',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'old_values' => json_encode(['status' => 'active']),
                'new_values' => json_encode(['status' => 'suspended', 'reason' => $reason]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return true;
        });
    }

    public function activateUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Update the users table status column
            $user->status = 'active';
            $user->save();
            
            // Create status record
            UserStatus::create([
                'user_id' => $user->id,
                'status' => 'active',
                'reason' => 'Account reactivated',
                'reactivated_by' => Auth::guard('admin')->id(),
                'reactivated_at' => now(),
            ]);

            AdminLog::create([
                'admin_user_id' => Auth::guard('admin')->id(),
                'action' => 'USER_ACTIVATED',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'old_values' => json_encode(['status' => 'suspended']),
                'new_values' => json_encode(['status' => 'active']),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return true;
        });
    }

    public function getCurrentStatus(User $user): ?UserStatus
    {
        return $user->userStatus;
    }

    public function getStatusHistory(User $user)
    {
        return $user->statusHistory;
    }
}
