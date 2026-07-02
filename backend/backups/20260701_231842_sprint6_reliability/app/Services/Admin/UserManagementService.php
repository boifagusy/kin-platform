<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\CheckIn;
use App\Models\TrustedContact;
use App\Services\SafetyScoreService;
use Illuminate\Support\Facades\DB;

class UserManagementService
{
    protected $safetyScoreService;

    public function __construct(SafetyScoreService $safetyScoreService)
    {
        $this->safetyScoreService = $safetyScoreService;
    }

    public function getUsersList(array $filters = [], int $perPage = 20)
    {
        $query = User::withCount([
            'trustedContacts',
            'trustedContacts as verified_contacts_count' => function ($q) {
                $q->where('verified', true);
            }
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate($perPage);

        $userIds = $users->pluck('id')->toArray();
        
        if (!empty($userIds)) {
            $metrics = $this->getBulkMetrics($userIds);
            
            foreach ($users as $user) {
                $userMetrics = $metrics[$user->id] ?? [];
                $user->safety_score = $this->safetyScoreService->getForUser($user);
                $user->missed_checkins_30d = $userMetrics['missed_checkins'] ?? 0;
                $user->duress_count = $userMetrics['duress_count'] ?? 0;
                $user->last_checkin = $userMetrics['last_checkin'] ?? null;
                $user->status = $this->determineStatus($user);
            }
        }

        return $users;
    }

    public function getUserDetail($id)
    {
        $user = User::with('trustedContacts')->find($id);
        
        if (!$user) {
            return null;
        }
        
        // Safety Score
        $user->safety_score = $this->safetyScoreService->getForUser($user);
        
        // Safety Metrics (last 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        
        $user->missed_checkins_30d = ActivityLog::where('user_id', $user->id)
            ->where('type', 'CHECKIN_MISSED')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->count();
        
        $user->sos_count_30d = ActivityLog::where('user_id', $user->id)
            ->where('type', 'SOS_TRIGGERED')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->count();
        
        $user->duress_count_30d = ActivityLog::where('user_id', $user->id)
            ->where('type', 'DURESS_PIN_USED')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->count();
        
        // Last check-in
        $user->last_checkin = CheckIn::where('user_id', $user->id)
            ->orderBy('checked_in_at', 'desc')
            ->first();
        
        // Next scheduled check-in (from settings)
        $user->next_checkin = $this->getNextCheckin($user);
        
        // Trusted contacts (Kin Circle)
        $user->trusted_contacts = TrustedContact::where('user_id', $user->id)->get();
        
        // Determine risk level
        $user->risk_level = $this->determineRiskLevel($user);
        
        return $user;
    }
    
    private function getNextCheckin($user)
    {
        // Default check-in time (can be extended from checkin_settings)
        return now()->setTime(18, 0); // 6 PM default
    }
    
    private function determineRiskLevel($user): string
    {
        $score = $user->safety_score ?? 100;
        
        if ($score >= 80) {
            return 'LOW';
        } elseif ($score >= 60) {
            return 'MEDIUM';
        } else {
            return 'HIGH';
        }
    }

    private function getBulkMetrics(array $userIds): array
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        $missedCheckins = ActivityLog::whereIn('user_id', $userIds)
            ->where('type', 'CHECKIN_MISSED')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $duressCounts = ActivityLog::whereIn('user_id', $userIds)
            ->where('type', 'DURESS_PIN_USED')
            ->where('occurred_at', '>=', $thirtyDaysAgo)
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $lastCheckins = CheckIn::whereIn('user_id', $userIds)
            ->orderBy('checked_in_at', 'desc')
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => $items->first());

        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = [
                'missed_checkins' => $missedCheckins[$userId]->count ?? 0,
                'duress_count' => $duressCounts[$userId]->count ?? 0,
                'last_checkin' => $lastCheckins[$userId] ?? null,
            ];
        }

        return $results;
    }

    private function determineStatus(User $user): string
    {
        if (!$user->last_checkin_at && !$user->last_login_at) {
            return 'inactive';
        }
        
        $lastActivity = $user->last_checkin_at ?? $user->last_login_at;
        if ($lastActivity && $lastActivity >= now()->subDays(7)) {
            return 'active';
        }
        
        return 'inactive';
    }

    public function getStats(): array
    {
        $sevenDaysAgo = now()->subDays(7);
        
        return [
            'total' => User::count(),
            'active' => User::where('last_checkin_at', '>=', $sevenDaysAgo)->count(),
            'new_7d' => User::where('created_at', '>=', $sevenDaysAgo)->count(),
            'missed_30d' => ActivityLog::where('type', 'CHECKIN_MISSED')
                ->where('occurred_at', '>=', now()->subDays(30))
                ->distinct('user_id')
                ->count('user_id'),
            'duress_30d' => ActivityLog::where('type', 'DURESS_PIN_USED')
                ->where('occurred_at', '>=', now()->subDays(30))
                ->distinct('user_id')
                ->count('user_id'),
            'high_risk' => 0,
        ];
    }
}
