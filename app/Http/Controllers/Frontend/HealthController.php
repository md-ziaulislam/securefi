<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * Public Heartbeat and Health Check Endpoint.
     * Used by external uptime monitors (UptimeRobot, Better Stack, Pingdom).
     */
    public function check(): JsonResponse
    {
        $startTime = microtime(true);
        $status = 'healthy';
        $statusCode = 200;
        $checks = [];

        // 1. Database Connectivity Check
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'connected',
                'driver' => DB::connection()->getDriverName(),
            ];
        } catch (\Throwable $e) {
            $status = 'unhealthy';
            $statusCode = 503;
            $checks['database'] = [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }

        // 2. Storage Writable Check
        try {
            $testFile = 'health_check_' . time() . '.tmp';
            Storage::disk('local')->put($testFile, 'ok');
            Storage::disk('local')->delete($testFile);
            $checks['storage'] = [
                'status' => 'writable',
            ];
        } catch (\Throwable $e) {
            $status = 'degraded';
            $checks['storage'] = [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }

        $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'status' => $status,
            'app_name' => config('app.name', 'SecuroFi.Tech'),
            'timestamp' => now()->toIso8601String(),
            'latency_ms' => $latencyMs,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'checks' => $checks,
        ], $statusCode, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
