<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ErrorMonitoringService;
use Illuminate\Http\Request;

class ErrorMonitoringController extends Controller
{
    /**
     * Display the Error Monitoring (Sentry) management console.
     */
    public function index(ErrorMonitoringService $sentry)
    {
        $settings = Setting::where('group', 'sentry')->pluck('value', 'key');
        $isConfigured = $sentry->isConfigured();
        $isEnabled = $sentry->isEnabled();
        $lastErrorAt = Setting::get('sentry_last_error_at');
        $parsedDsn = $sentry->parseDsn();

        return view('admin.errors.index', compact(
            'settings',
            'isConfigured',
            'isEnabled',
            'lastErrorAt',
            'parsedDsn'
        ));
    }

    /**
     * Update Sentry error monitoring configuration settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'error_monitoring_enabled'   => 'nullable|boolean',
            'sentry_dsn'                 => 'nullable|string|max:255',
            'sentry_traces_sample_rate'  => 'nullable|numeric|min:0|max:1',
            'sentry_send_user_context'   => 'nullable|boolean',
        ]);

        Setting::set('error_monitoring_enabled', $request->has('error_monitoring_enabled') ? '1' : '0', 'sentry');
        Setting::set('sentry_send_user_context', $request->has('sentry_send_user_context') ? '1' : '0', 'sentry');

        if ($request->filled('sentry_dsn')) {
            Setting::set('sentry_dsn', trim($validated['sentry_dsn']), 'sentry');
        }

        if ($request->filled('sentry_traces_sample_rate')) {
            Setting::set('sentry_traces_sample_rate', $validated['sentry_traces_sample_rate'], 'sentry');
        }

        return redirect()->route('admin.errors.index')
            ->with('success', 'Sentry error monitoring configuration saved successfully.');
    }

    /**
     * Dispatch an intentional test exception to verify live Sentry connectivity.
     */
    public function test(ErrorMonitoringService $sentry)
    {
        if (!$sentry->isConfigured()) {
            return redirect()->route('admin.errors.index')
                ->with('error', 'Please configure your Sentry DSN before running a test.');
        }

        $result = $sentry->testCapture();

        if ($result['success']) {
            return redirect()->route('admin.errors.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.errors.index')
            ->with('error', $result['message']);
    }
}
