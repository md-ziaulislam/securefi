<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareCdnService
{
    protected const API_BASE = 'https://api.cloudflare.com/client/v4';

    /**
     * Check if CDN integration is enabled in settings.
     */
    public function isEnabled(): bool
    {
        return Setting::get('cdn_enabled', '0') === '1';
    }

    /**
     * Retrieve the configured Cloudflare Zone ID.
     */
    public function getZoneId(): ?string
    {
        return Setting::get('cloudflare_zone_id');
    }

    /**
     * Retrieve the configured Cloudflare API Token.
     */
    public function getApiToken(): ?string
    {
        return Setting::get('cloudflare_api_token');
    }

    /**
     * Check if minimum required credentials (Zone ID & Token) are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getZoneId()) && !empty($this->getApiToken());
    }

    /**
     * Get the timestamp of the last successful cache purge.
     */
    public function getLastPurgedAt(): ?string
    {
        return Setting::get('cdn_last_purged_at');
    }

    /**
     * Purge all cached assets from Cloudflare Edge.
     */
    public function purgeEverything(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Cloudflare Zone ID and API Token must be configured first.',
                'errors' => ['Missing API credentials.'],
            ];
        }

        $zoneId = trim($this->getZoneId());
        $token = trim($this->getApiToken());
        $url = self::API_BASE . "/zones/{$zoneId}/purge_cache";

        try {
            $response = Http::withToken($token)
                ->timeout(15)
                ->post($url, [
                    'purge_everything' => true,
                ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['success'])) {
                Setting::set('cdn_last_purged_at', now()->toIso8601String(), 'cdn');
                Setting::set('cdn_last_purge_type', 'all', 'cdn');

                Log::info('Cloudflare CDN: Entire cache purged successfully.');

                return [
                    'success' => true,
                    'message' => 'Entire Cloudflare edge cache purged successfully. Changes will be reflected globally within 30 seconds.',
                    'errors' => [],
                ];
            }

            $errorMessage = $data['errors'][0]['message'] ?? 'Failed to purge cache. Please check your API Token permissions.';
            Log::warning('Cloudflare CDN purge failed', ['response' => $data]);

            return [
                'success' => false,
                'message' => "Cloudflare API error: {$errorMessage}",
                'errors' => array_column($data['errors'] ?? [], 'message'),
            ];
        } catch (\Throwable $e) {
            Log::error('Cloudflare CDN API exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Network error connecting to Cloudflare API: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Purge specific URLs from Cloudflare Edge cache.
     *
     * @param array $urls List of full URLs to purge (e.g. ['https://securofi.tech/style.css'])
     */
    public function purgeUrls(array $urls): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Cloudflare Zone ID and API Token must be configured first.',
                'errors' => ['Missing API credentials.'],
            ];
        }

        // Clean and filter URLs
        $cleanUrls = array_values(array_filter(array_map('trim', $urls), function ($url) {
            return filter_var($url, FILTER_VALIDATE_URL);
        }));

        if (empty($cleanUrls)) {
            return [
                'success' => false,
                'message' => 'No valid URLs provided to purge. Please provide absolute URLs (e.g., https://example.com/asset.css).',
                'errors' => ['Invalid URL list.'],
            ];
        }

        // Cloudflare allows up to 30 URLs per single purge request
        $chunks = array_chunk($cleanUrls, 30);
        $zoneId = trim($this->getZoneId());
        $token = trim($this->getApiToken());
        $url = self::API_BASE . "/zones/{$zoneId}/purge_cache";

        $allErrors = [];
        $purgedCount = 0;

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withToken($token)
                    ->timeout(15)
                    ->post($url, [
                        'files' => $chunk,
                    ]);

                $data = $response->json();

                if ($response->successful() && !empty($data['success'])) {
                    $purgedCount += count($chunk);
                } else {
                    $msg = $data['errors'][0]['message'] ?? 'Failed to purge chunk of URLs.';
                    $allErrors[] = $msg;
                }
            } catch (\Throwable $e) {
                $allErrors[] = $e->getMessage();
            }
        }

        if ($purgedCount > 0) {
            Setting::set('cdn_last_purged_at', now()->toIso8601String(), 'cdn');
            Setting::set('cdn_last_purge_type', "urls ({$purgedCount})", 'cdn');

            return [
                'success' => empty($allErrors),
                'message' => "Successfully purged {$purgedCount} URL(s) from Cloudflare cache." . (!empty($allErrors) ? ' Some URLs encountered errors.' : ''),
                'errors' => $allErrors,
                'purged_count' => $purgedCount,
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to purge any URLs. Check Cloudflare API Token permissions.',
            'errors' => $allErrors,
        ];
    }

    /**
     * Test credentials against Cloudflare API by querying Zone details.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Both Zone ID and API Token must be filled.',
            ];
        }

        $zoneId = trim($this->getZoneId());
        $token = trim($this->getApiToken());

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get(self::API_BASE . "/zones/{$zoneId}");

            $data = $response->json();

            if ($response->successful() && !empty($data['success'])) {
                $zoneName = $data['result']['name'] ?? 'Unknown';
                $status = $data['result']['status'] ?? 'active';
                $plan = $data['result']['plan']['name'] ?? 'Free';

                return [
                    'success' => true,
                    'message' => "Connected to Cloudflare! Zone: {$zoneName} (Plan: {$plan}, Status: {$status}).",
                    'details' => [
                        'zone_name' => $zoneName,
                        'status' => $status,
                        'plan' => $plan,
                    ],
                ];
            }

            $errorMessage = $data['errors'][0]['message'] ?? 'Unable to verify Zone ID or API Token.';

            return [
                'success' => false,
                'message' => "Cloudflare Verification Failed: {$errorMessage}",
                'errors' => array_column($data['errors'] ?? [], 'message'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection to Cloudflare API timed out or failed: ' . $e->getMessage(),
            ];
        }
    }
}
