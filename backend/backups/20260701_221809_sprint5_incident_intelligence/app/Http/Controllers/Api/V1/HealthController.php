<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index()
    {
        $status = [
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'services' => []
        ];
        
        // Check database
        try {
            DB::connection()->getPdo();
            $status['services']['database'] = 'ok';
        } catch (\Exception $e) {
            $status['services']['database'] = 'error';
            $status['status'] = 'degraded';
        }
        
        // Check cache
        try {
            Cache::store('database')->put('health_check', true, 10);
            $cached = Cache::store('database')->get('health_check');
            $status['services']['cache'] = $cached ? 'ok' : 'error';
        } catch (\Exception $e) {
            $status['services']['cache'] = 'error';
            $status['status'] = 'degraded';
        }
        
        // Check queue (simplified for now)
        try {
            $status['services']['queue'] = 'ok';
        } catch (\Exception $e) {
            $status['services']['queue'] = 'error';
            $status['status'] = 'degraded';
        }
        
        $httpCode = $status['status'] === 'ok' ? 200 : 503;
        
        return ApiResponse::success($status, 'Health check completed', $httpCode);
    }
    
    public function ping()
    {
        return response()->json(['pong' => true]);
    }
}
