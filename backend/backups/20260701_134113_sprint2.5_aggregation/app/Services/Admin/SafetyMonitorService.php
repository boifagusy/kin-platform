<?php

namespace App\Services\Admin;

use App\Models\SosEvent;
use App\Models\ActivityLog;
use App\Models\EmergencyEscalation;
use App\Models\AlertNote;
use Illuminate\Support\Facades\Cache;

class SafetyMonitorService
{
    public function getSafetyMetrics(): array
    {
        return Cache::remember('admin.safety.metrics', 60, function () {
            return [
                'active_sos' => ['count' => SosEvent::whereNull('resolved_at')->count(), 'trend' => 'neutral', 'change' => 0],
                'missed_today' => ['count' => ActivityLog::where('type', 'CHECKIN_MISSED')->whereDate('occurred_at', today())->count(), 'trend' => 'neutral', 'change' => 0],
                'duress_today' => ['count' => ActivityLog::where('type', 'DURESS_PIN_USED')->whereDate('occurred_at', today())->count(), 'trend' => 'neutral', 'change' => 0],
                'pending_escalations' => ['count' => EmergencyEscalation::where('status', 'active')->count(), 'trend' => 'neutral', 'change' => 0],
                'active_issues' => $this->getActiveIssues(),
                'safety_score_trend' => [
                    'data' => [['day' => 'Mon', 'score' => 95], ['day' => 'Tue', 'score' => 92], ['day' => 'Wed', 'score' => 94], ['day' => 'Thu', 'score' => 96], ['day' => 'Fri', 'score' => 98], ['day' => 'Sat', 'score' => 97], ['day' => 'Sun', 'score' => 95]],
                    'current' => 95, 'trend' => 'up', 'change' => 2.5
                ],
                'last_updated' => now(),
            ];
        });
    }

    protected function getActiveIssues(): array
    {
        $issues = [];
        foreach (SosEvent::whereNull('resolved_at')->with('user')->latest('triggered_at')->take(5)->get() as $sos) {
            $issues[] = ['type' => 'sos', 'id' => $sos->id, 'user_id' => $sos->user_id, 'user_name' => $sos->user->name ?? 'Unknown', 'user_phone' => $sos->user->phone ?? 'N/A', 'created_at' => $sos->created_at?->toISOString()];
        }
        foreach (EmergencyEscalation::where('status', 'active')->with('user')->latest('created_at')->take(5)->get() as $esc) {
            $issues[] = ['type' => 'escalation', 'id' => $esc->id, 'escalation_type' => $esc->escalation_type, 'priority' => $esc->priority, 'user_id' => $esc->user_id, 'user_name' => $esc->user->name ?? 'Unknown', 'user_phone' => $esc->user->phone ?? 'N/A', 'created_at' => $esc->created_at?->toISOString()];
        }
        usort($issues, fn($a, $b) => ($b['created_at'] ?? '') <=> ($a['created_at'] ?? ''));
        return array_slice($issues, 0, 10);
    }

    public function getAlertsList(array $filters = [], int $perPage = 20)
    {
        $query = EmergencyEscalation::with(['user', 'assignedAdmin'])->whereIn('status', ['active', 'pending']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['priority'])) $query->where('priority', $filters['priority']);
        if (!empty($filters['search'])) $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$filters['search']}%")->orWhere('phone', 'like', "%{$filters['search']}%"));
        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')->paginate($perPage);
    }
    
    public function getAlertDetail(int $id): ?EmergencyEscalation { return EmergencyEscalation::with(['user', 'assignedAdmin', 'resolver'])->find($id); }
    public function assignAlert(int $alertId, int $adminId): bool { return EmergencyEscalation::findOrFail($alertId)->update(['assigned_admin_id' => $adminId]); }
    
    public function resolveAlert(int $alertId, int $adminId, string $resolutionNote): bool
    {
        $this->addAlertNote($alertId, $adminId, 'RESOLVED: ' . $resolutionNote);
        $alert = EmergencyEscalation::findOrFail($alertId);
        $alert->status = 'resolved';
        $alert->resolved_by = $adminId;
        $alert->resolved_at = now();
        return $alert->save();
    }
    
    public function escalateAlert(int $alertId): bool
    {
        $alert = EmergencyEscalation::findOrFail($alertId);
        $priorityOrder = ['low', 'medium', 'high', 'critical'];
        $current = strtolower($alert->priority);
        $index = array_search($current, $priorityOrder);
        if ($index !== false && $index < 3) {
            $alert->priority = $priorityOrder[$index + 1];
            return $alert->save();
        }
        return true;
    }
    
    public function addAlertNote(int $alertId, int $adminId, string $note): AlertNote { return AlertNote::create(['alert_id' => $alertId, 'admin_id' => $adminId, 'note' => $note]); }
    public function getAlertNotes(int $alertId) { return AlertNote::with('admin')->where('alert_id', $alertId)->orderBy('created_at', 'desc')->get(); }
}
