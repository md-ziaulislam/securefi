<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ErrorMonitoringService
{
    /**
     * Check if Error Monitoring is enabled and configured.
     */
    public function isEnabled(): bool
    {
        return Setting::get('error_monitoring_enabled', '0') === '1' && $this->isConfigured();
    }

    /**
     * Check if Sentry DSN is set.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getDsn());
    }

    /**
     * Retrieve the configured Sentry DSN.
     */
    public function getDsn(): ?string
    {
        return Setting::get('sentry_dsn');
    }

    /**
     * Parse Sentry DSN into its structural components.
     * Expected format: https://{PUBLIC_KEY}@{HOST}/{PROJECT_ID}
     */
    public function parseDsn(?string $dsn = null): ?array
    {
        $dsn = $dsn ?: $this->getDsn();
        if (empty($dsn)) {
            return null;
        }

        $parsed = parse_url(trim($dsn));
        if (!$parsed || empty($parsed['host']) || empty($parsed['path']) || empty($parsed['user'])) {
            return null;
        }

        $projectId = trim($parsed['path'], '/');
        $scheme = $parsed['scheme'] ?? 'https';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $host = $parsed['host'];
        $publicKey = $parsed['user'];

        return [
            'endpoint' => "{$scheme}://{$host}{$port}/api/{$projectId}/store/",
            'project_id' => $projectId,
            'public_key' => $publicKey,
            'host' => $host,
        ];
    }

    /**
     * Capture an unhandled exception and send payload to Sentry API.
     */
    public function captureException(\Throwable $e): array
    {
        $parsed = $this->parseDsn();
        if (!$parsed) {
            return [
                'success' => false,
                'message' => 'Sentry DSN is missing or invalid. Please configure your DSN in settings.',
            ];
        }

        $eventId = str_replace('-', '', Str::uuid()->toString());
        $timestamp = now()->toISOString();

        // Format stack trace (up to 30 frames)
        $frames = [];
        foreach (array_slice($e->getTrace(), 0, 30) as $frame) {
            $frames[] = [
                'filename' => $frame['file'] ?? '[internal]',
                'lineno' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
                'module' => isset($frame['class']) ? $frame['class'] : null,
            ];
        }

        $payload = [
            'event_id' => $eventId,
            'timestamp' => $timestamp,
            'level' => 'error',
            'platform' => 'php',
            'logger' => 'laravel',
            'environment' => config('app.env', 'production'),
            'server_name' => request()->getHost() ?: gethostname(),
            'release' => config('app.name', 'SecuroFi') . '@1.0.0',
            'exception' => [
                'values' => [
                    [
                        'type' => get_class($e),
                        'value' => $e->getMessage(),
                        'stacktrace' => [
                            'frames' => array_reverse($frames),
                        ],
                    ],
                ],
            ],
            'tags' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
        ];

        // Add HTTP Request Context if running in web context
        if (!app()->runningInConsole() && request()) {
            $payload['request'] = [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'query_string' => request()->getQueryString(),
                'headers' => [
                    'User-Agent' => request()->userAgent(),
                ],
            ];
        }

        // Add User Context if enabled & authenticated
        if (Setting::get('sentry_send_user_context', '1') === '1' && Auth::check()) {
            $payload['user'] = [
                'id' => Auth::id(),
                'email' => Auth::user()->email,
                'username' => Auth::user()->name,
            ];
        }

        try {
            $authHeader = sprintf(
                'Sentry sentry_version=7, sentry_client=securofi/1.0, sentry_key=%s, sentry_timestamp=%d',
                $parsed['public_key'],
                time()
            );

            $response = Http::withHeaders([
                'X-Sentry-Auth' => $authHeader,
                'Content-Type' => 'application/json',
            ])
            ->timeout(6)
            ->post($parsed['endpoint'], $payload);

            if ($response->successful() || $response->status() === 200) {
                Setting::set('sentry_last_error_at', now()->toIso8601String(), 'sentry');

                return [
                    'success' => true,
                    'event_id' => $eventId,
                    'message' => "Exception successfully reported to Sentry. Event ID: {$eventId}",
                ];
            }

            Log::warning('Sentry API rejected exception payload: ' . $response->body());

            return [
                'success' => false,
                'message' => 'Sentry responded with status ' . $response->status() . ': ' . $response->body(),
            ];
        } catch (\Throwable $ex) {
            Log::error('Error dispatching exception to Sentry: ' . $ex->getMessage());

            return [
                'success' => false,
                'message' => 'Connection to Sentry timed out or failed: ' . $ex->getMessage(),
            ];
        }
    }

    /**
     * Send an intentional test exception to verify live Sentry connectivity.
     */
    public function testCapture(): array
    {
        $testException = new \RuntimeException(
            'SecuroFi Diagnostics: Test Exception verified from Admin Console at ' . now()->toIso8601String()
        );

        return $this->captureException($testException);
    }
}
