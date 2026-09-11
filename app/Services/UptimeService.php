<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UptimeService
{
    protected const UPTIMEROBOT_API = 'https://api.uptimerobot.com/v2/getMonitors';
    protected const CACHE_KEY = 'uptime_monitors_data';
    protected const CACHE_TTL = 180; // 3 minutes

    /**
     * Check if Uptime Monitoring API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getApiKey());
    }

    /**
     * Retrieve the configured API Key.
     */
    public function getApiKey(): ?string
    {
        return Setting::get('uptime_api_key');
    }

    /**
     * Retrieve the configured provider name.
     */
    public function getProvider(): string
    {
        return Setting::get('uptime_provider', 'uptimerobot');
    }

    /**
     * Check if Uptime widget is enabled on the main Admin Dashboard.
     */
    public function isWidgetEnabled(): bool
    {
        return Setting::get('uptime_widget_enabled', '1') === '1';
    }

    /**
     * Fetch monitors and uptime ratios from UptimeRobot (or cached).
     */
    public function getUptimeData(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchFromProvider();
        });
    }

    /**
     * Force refresh by clearing cached uptime metrics.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Query UptimeRobot API v2 for monitor statistics.
     */
    protected function fetchFromProvider(): array
    {
        $apiKey = trim($this->getApiKey() ?? '');

        if (empty($apiKey)) {
            return [
                'configured' => false,
                'provider' => $this->getProvider(),
                'status' => 'standby',
                'message' => 'Uptime API key not configured. Add your UptimeRobot API key below to display live metrics.',
                'monitors' => [],
                'summary' => [
                    'ratio_24h' => '100.00',
                    'ratio_7d' => '100.00',
                    'ratio_30d' => '100.00',
                    'avg_response_time' => 0,
                    'status_label' => 'Standby (Local Health Active)',
                    'is_up' => true,
                ],
            ];
        }

        try {
            $response = Http::asForm()
                ->timeout(12)
                ->post(self::UPTIMEROBOT_API, [
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'logs' => 1,
                    'log_types' => '1-2', // 1: down, 2: up
                    'response_times' => 1,
                    'response_times_limit' => 24,
                    'custom_uptime_ratios' => '1-7-30',
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['stat']) && $data['stat'] === 'ok') {
                $rawMonitors = $data['monitors'] ?? [];
                $monitors = [];

                $total30dRatio = 0;
                $total24hRatio = 0;
                $total7dRatio = 0;
                $totalResponseTime = 0;
                $allUp = true;
                $count = count($rawMonitors);

                foreach ($rawMonitors as $m) {
                    $statusNum = (int) ($m['status'] ?? 0);
                    // 0: paused, 1: not checked, 2: up, 8: seems down, 9: down
                    $isUp = ($statusNum === 2);
                    if (!$isUp && $statusNum !== 0) {
                        $allUp = false;
                    }

                    $ratios = explode('-', $m['custom_uptime_ratio'] ?? '100-100-100');
                    $r24h = (float) ($ratios[0] ?? 100);
                    $r7d = (float) ($ratios[1] ?? 100);
                    $r30d = (float) ($ratios[2] ?? 100);

                    $total24hRatio += $r24h;
                    $total7dRatio += $r7d;
                    $total30dRatio += $r30d;

                    $avgResp = (float) ($m['average_response_time'] ?? 0);
                    $totalResponseTime += $avgResp;

                    $statusLabels = [
                        0 => 'Paused',
                        1 => 'Pending',
                        2 => 'Operational (Up)',
                        8 => 'Degraded',
                        9 => 'Down / Outage',
                    ];

                    $monitors[] = [
                        'id' => $m['id'] ?? '',
                        'name' => $m['friendly_name'] ?? 'Monitor',
                        'url' => $m['url'] ?? '',
                        'status' => $statusNum,
                        'status_label' => $statusLabels[$statusNum] ?? 'Unknown',
                        'is_up' => $isUp,
                        'ratio_24h' => number_format($r24h, 2),
                        'ratio_7d' => number_format($r7d, 2),
                        'ratio_30d' => number_format($r30d, 2),
                        'avg_response_time' => round($avgResp, 1),
                        'response_times' => $m['response_times'] ?? [],
                        'logs' => $m['logs'] ?? [],
                    ];
                }

                $summary = [
                    'ratio_24h' => $count > 0 ? number_format($total24hRatio / $count, 2) : '100.00',
                    'ratio_7d' => $count > 0 ? number_format($total7dRatio / $count, 2) : '100.00',
                    'ratio_30d' => $count > 0 ? number_format($total30dRatio / $count, 2) : '100.00',
                    'avg_response_time' => $count > 0 ? round($totalResponseTime / $count, 1) : 0,
                    'status_label' => $allUp ? 'All Systems Operational' : 'Active Outage / Degradation',
                    'is_up' => $allUp,
                    'monitor_count' => $count,
                ];

                return [
                    'configured' => true,
                    'provider' => 'uptimerobot',
                    'status' => $allUp ? 'operational' : 'outage',
                    'message' => 'Live metrics retrieved from UptimeRobot.',
                    'monitors' => $monitors,
                    'summary' => $summary,
                    'last_checked_at' => now()->toIso8601String(),
                ];
            }

            $errorMessage = $data['error']['message'] ?? 'Unable to authenticate with UptimeRobot API. Verify your API Key.';
            Log::warning('UptimeRobot API error', ['data' => $data]);

            return [
                'configured' => true,
                'provider' => 'uptimerobot',
                'status' => 'error',
                'message' => $errorMessage,
                'monitors' => [],
                'summary' => [
                    'ratio_24h' => '—',
                    'ratio_7d' => '—',
                    'ratio_30d' => '—',
                    'avg_response_time' => 0,
                    'status_label' => 'API Error: ' . $errorMessage,
                    'is_up' => false,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('UptimeService API exception: ' . $e->getMessage());

            return [
                'configured' => true,
                'provider' => 'uptimerobot',
                'status' => 'error',
                'message' => 'Connection timed out or failed: ' . $e->getMessage(),
                'monitors' => [],
                'summary' => [
                    'ratio_24h' => '—',
                    'ratio_7d' => '—',
                    'ratio_30d' => '—',
                    'avg_response_time' => 0,
                    'status_label' => 'Connection Failed',
                    'is_up' => false,
                ],
            ];
        }
    }

    /**
     * Get system-level telemetry and health metrics.
     */
    public function getSystemHealth(): array
    {
        $startTime = microtime(true);
        $dbConnected = false;
        $dbDriver = 'unknown';

        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $dbDriver = DB::connection()->getDriverName();
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $latency = round((microtime(true) - $startTime) * 1000, 2);

        // Disk space
        $diskFree = @disk_free_space(storage_path());
        $diskTotal = @disk_total_space(storage_path());
        $diskUsagePct = ($diskTotal > 0 && $diskFree !== false) ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 1) : null;

        // Memory
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        return [
            'database' => [
                'connected' => $dbConnected,
                'driver' => $dbDriver,
            ],
            'latency_ms' => $latency,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage_mb' => $memoryUsage,
            'memory_peak_mb' => $memoryPeak,
            'disk_free_gb' => $diskFree ? round($diskFree / 1024 / 1024 / 1024, 2) : null,
            'disk_usage_pct' => $diskUsagePct,
            'heartbeat_url' => url('/health'),
        ];
    }
}
