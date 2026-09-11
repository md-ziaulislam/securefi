<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CloudflareCdnService;
use Illuminate\Http\Request;

class CdnController extends Controller
{
    /**
     * Display CDN & Cache Purge dashboard.
     */
    public function index(CloudflareCdnService $cdnService)
    {
        $settings = Setting::where('group', 'cdn')->pluck('value', 'key');

        $isConfigured = $cdnService->isConfigured();
        $isEnabled = $cdnService->isEnabled();
        $lastPurgedAt = $cdnService->getLastPurgedAt();
        $lastPurgeType = Setting::get('cdn_last_purge_type', 'none');

        return view('admin.cdn.index', compact(
            'settings',
            'isConfigured',
            'isEnabled',
            'lastPurgedAt',
            'lastPurgeType'
        ));
    }

    /**
     * Update CDN configuration settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'cdn_enabled'            => 'nullable|boolean',
            'cloudflare_zone_id'     => 'nullable|string|max:100',
            'cloudflare_api_token'   => 'nullable|string|max:255',
            'cdn_custom_domain'      => 'nullable|url|max:255',
            'cdn_auto_purge'         => 'nullable|boolean',
        ]);

        Setting::set('cdn_enabled', $request->has('cdn_enabled') ? '1' : '0', 'cdn');
        Setting::set('cdn_auto_purge', $request->has('cdn_auto_purge') ? '1' : '0', 'cdn');

        if ($request->filled('cloudflare_zone_id')) {
            Setting::set('cloudflare_zone_id', trim($validated['cloudflare_zone_id']), 'cdn');
        }

        // Only update API token if a new one was provided
        if ($request->filled('cloudflare_api_token')) {
            Setting::set('cloudflare_api_token', trim($validated['cloudflare_api_token']), 'cdn');
        }

        if ($request->filled('cdn_custom_domain')) {
            Setting::set('cdn_custom_domain', rtrim($validated['cdn_custom_domain'], '/'), 'cdn');
        } else {
            Setting::set('cdn_custom_domain', '', 'cdn');
        }

        return redirect()->route('admin.cdn.index')
            ->with('success', 'CDN configuration and Cloudflare API settings saved successfully.');
    }

    /**
     * Purge the entire Cloudflare Edge cache.
     */
    public function purgeAll(CloudflareCdnService $cdnService)
    {
        $result = $cdnService->purgeEverything();

        if ($result['success']) {
            return redirect()->route('admin.cdn.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.cdn.index')
            ->with('error', $result['message']);
    }

    /**
     * Purge individual specified URLs.
     */
    public function purgeUrls(Request $request, CloudflareCdnService $cdnService)
    {
        $request->validate([
            'purge_urls' => 'required|string',
        ]);

        $rawUrls = preg_split('/[\r\n,]+/', $request->input('purge_urls'));
        $urls = array_filter(array_map('trim', $rawUrls));

        if (empty($urls)) {
            return redirect()->route('admin.cdn.index')
                ->with('error', 'Please provide at least one valid URL to purge.');
        }

        $result = $cdnService->purgeUrls($urls);

        if ($result['success']) {
            return redirect()->route('admin.cdn.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.cdn.index')
            ->with('error', $result['message']);
    }

    /**
     * Test connection to Cloudflare using configured credentials.
     */
    public function testConnection(Request $request, CloudflareCdnService $cdnService)
    {
        $result = $cdnService->testConnection();

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('admin.cdn.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.cdn.index')
            ->with('error', $result['message']);
    }
}
