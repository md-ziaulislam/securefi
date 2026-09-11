<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\UptimeService;
use Illuminate\Http\Request;

class UptimeController extends Controller
{
    /**
     * Display the Uptime Monitoring & Health Telemetry Dashboard.
     */
    public function index(UptimeService $uptimeService)
    {
        $settings = Setting::where('group', 'uptime')->pluck('value', 'key');
        $uptimeData = $uptimeService->getUptimeData();
        $health = $uptimeService->getSystemHealth();

        return view('admin.uptime.index', compact('settings', 'uptimeData', 'health'));
    }

    /**
     * Update Uptime Monitoring settings.
     */
    public function updateSettings(Request $request, UptimeService $uptimeService)
    {
        $validated = $request->validate([
            'uptime_provider'        => 'nullable|string|in:uptimerobot,betteruptime,custom',
            'uptime_api_key'         => 'nullable|string|max:255',
            'uptime_widget_enabled'  => 'nullable|boolean',
        ]);

        Setting::set('uptime_provider', $validated['uptime_provider'] ?? 'uptimerobot', 'uptime');
        Setting::set('uptime_widget_enabled', $request->has('uptime_widget_enabled') ? '1' : '0', 'uptime');

        if ($request->filled('uptime_api_key')) {
            Setting::set('uptime_api_key', trim($validated['uptime_api_key']), 'uptime');
        }

        // Flush cached uptime data so new settings take effect immediately
        $uptimeService->clearCache();

        return redirect()->route('admin.uptime.index')
            ->with('success', 'Uptime monitoring parameters updated and cache cleared.');
    }

    /**
     * Force refresh live uptime statistics from external provider.
     */
    public function refresh(UptimeService $uptimeService)
    {
        $uptimeService->clearCache();
        $uptimeService->getUptimeData();

        return redirect()->route('admin.uptime.index')
            ->with('success', 'Live uptime statistics refreshed from external provider.');
    }
}
