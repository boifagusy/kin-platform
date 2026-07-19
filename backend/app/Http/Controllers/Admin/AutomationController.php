<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AutomationService;
use App\Services\RateLimiter;
use App\Services\QuietHoursResolver;
use App\Services\ChannelFallbackResolver;
use App\Services\NotificationDriverManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutomationController extends Controller
{
    public function logs(Request $request)
    {
        // Read automation execution logs from Laravel log
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $automationLines = array_filter($lines, fn($l) => str_contains($l, '[N9]'));
            $recent = array_slice(array_reverse($automationLines), 0, 100);

            foreach ($recent as $line) {
                if (preg_match('/\[(.*?)\].*?\{.*"rule":"(.*?)".*"event":"(.*?)".*"user_id":(.*?)[,}]/', $line, $m)) {
                    $logs[] = [
                        'timestamp' => $m[1],
                        'rule' => $m[2],
                        'event' => $m[3],
                        'user_id' => $m[4] !== 'null' ? (int) $m[4] : null,
                    ];
                }
            }
        }

        return view('admin.automation.logs', ['logs' => $logs]);
    }

    public function test(Request $request)
    {
        $eventType = $request->input('event_type', 'sos_triggered');
        $userId = $request->input('user_id');

        $automation = new AutomationService(
            new RateLimiter(),
            new QuietHoursResolver(),
            new ChannelFallbackResolver(),
            app(NotificationDriverManager::class),
        );

        $payload = [
            'event_type' => $eventType,
            'user_id' => $userId,
            'incident_type' => $request->input('incident_type', 'test'),
        ];

        try {
            $automation->evaluate($eventType, $payload);
            return response()->json(['success' => true, 'message' => "Rule evaluation triggered for: {$eventType}"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
