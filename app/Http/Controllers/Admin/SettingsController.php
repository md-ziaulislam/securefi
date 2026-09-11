<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        $allTimezones = app(\App\Services\TimezoneService::class)->getAllTimezones();
        return view('admin.settings.index', compact('settings', 'allTimezones'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Timezone & Localization (PRD-ADDNEW 5.5)
            'site_timezone'                => 'nullable|string|max:64',
            'auto_detect_timezone'         => 'nullable|boolean',
            'date_format'                  => 'nullable|string|max:30',
            'time_format'                  => 'nullable|string|max:30',
            // Identity
            'site_name' => 'required|string|max:100',
            'site_tagline' => 'nullable|string|max:200',
            'site_description' => 'nullable|string|max:500',
            // Logos
            'site_logo_light' => 'nullable|string|max:500',
            'site_logo_light_file' => 'nullable|image|max:2048',
            'site_logo_dark' => 'nullable|string|max:500',
            'site_logo_dark_file' => 'nullable|image|max:2048',
            'site_favicon' => 'nullable|string|max:500',
            'site_favicon_file' => 'nullable|file|mimes:ico,png,svg|max:1024',
            // Social Links
            'social_twitter' => 'nullable|string|max:255',
            'social_github' => 'nullable|string|max:255',
            'social_linkedin' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            // Contact & Dev
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'dev_name' => 'nullable|string|max:100',
            'dev_title' => 'nullable|string|max:100',
            'dev_bio' => 'nullable|string|max:1000',
            'dev_skills' => 'nullable|string|max:500',
            'dev_email' => 'nullable|email|max:150',
            'dev_github' => 'nullable|string|max:255',
            'dev_linkedin' => 'nullable|string|max:255',
            'dev_twitter' => 'nullable|string|max:255',
            'dev_website' => 'nullable|string|max:255',
            'dev_photo_file' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'remove_dev_photo' => 'nullable|boolean',
            // Cookie Consent
            'cookie_consent_enabled'       => 'nullable|boolean',
            'cookie_consent_text'          => 'nullable|string|max:500',
            // Captcha / Bot Protection
            'captcha_enabled'              => 'nullable|boolean',
            'captcha_provider'             => 'nullable|in:recaptcha_v2,recaptcha_v3,turnstile',
            'captcha_site_key'             => 'nullable|string|max:255',
            'captcha_secret_key'           => 'nullable|string|max:255',
            'captcha_contact_enabled'      => 'nullable|boolean',
            'captcha_newsletter_enabled'   => 'nullable|boolean',
            'captcha_comment_enabled'      => 'nullable|boolean',
            'captcha_v3_threshold'         => 'nullable|numeric|min:0|max:1',
        ]);

        // Handle File uploads
        if ($request->hasFile('site_logo_light_file')) {
            $path = $request->file('site_logo_light_file')->store('uploads/branding', 'public');
            Setting::set('site_logo_light', Storage::url($path), 'branding');
        } elseif (isset($validated['site_logo_light'])) {
            Setting::set('site_logo_light', $validated['site_logo_light'], 'branding');
        }

        if ($request->hasFile('site_logo_dark_file')) {
            $path = $request->file('site_logo_dark_file')->store('uploads/branding', 'public');
            Setting::set('site_logo_dark', Storage::url($path), 'branding');
        } elseif (isset($validated['site_logo_dark'])) {
            Setting::set('site_logo_dark', $validated['site_logo_dark'], 'branding');
        }

        if ($request->hasFile('site_favicon_file')) {
            $path = $request->file('site_favicon_file')->store('uploads/branding', 'public');
            Setting::set('site_favicon', Storage::url($path), 'branding');
        } elseif (isset($validated['site_favicon'])) {
            Setting::set('site_favicon', $validated['site_favicon'], 'branding');
        }

        // Developer Photo upload & removal
        if ($request->boolean('remove_dev_photo')) {
            Setting::set('dev_photo', null, 'dev');
        } elseif ($request->hasFile('dev_photo_file')) {
            $path = $request->file('dev_photo_file')->store('uploads/developer', 'public');
            Setting::set('dev_photo', Storage::url($path), 'dev');
        }

        // Save simple string settings
        $fields = [
            'site_name' => 'general',
            'site_tagline' => 'general',
            'site_description' => 'general',
            'social_twitter' => 'social',
            'social_github' => 'social',
            'social_linkedin' => 'social',
            'social_youtube' => 'social',
            'contact_email' => 'contact',
            'contact_phone' => 'contact',
            'dev_name' => 'dev',
            'dev_title' => 'dev',
            'dev_bio' => 'dev',
            'dev_skills' => 'dev',
            'dev_email' => 'dev',
            'dev_github' => 'dev',
            'dev_linkedin' => 'dev',
            'dev_twitter' => 'dev',
            'dev_website' => 'dev',
            'cookie_consent_text' => 'general',
        ];

        foreach ($fields as $field => $group) {
            if (array_key_exists($field, $validated)) {
                Setting::set($field, $validated[$field], $group);
            }
        }

        Setting::set('cookie_consent_enabled', $request->has('cookie_consent_enabled') ? '1' : '0', 'general');

        // ── Captcha Settings ─────────────────────────────────────────────────
        Setting::set('captcha_enabled',            $request->has('captcha_enabled') ? '1' : '0', 'captcha');
        Setting::set('captcha_contact_enabled',    $request->has('captcha_contact_enabled') ? '1' : '0', 'captcha');
        Setting::set('captcha_newsletter_enabled', $request->has('captcha_newsletter_enabled') ? '1' : '0', 'captcha');
        Setting::set('captcha_comment_enabled',    $request->has('captcha_comment_enabled') ? '1' : '0', 'captcha');

        if ($request->filled('captcha_provider')) {
            Setting::set('captcha_provider', $validated['captcha_provider'], 'captcha');
        }
        if ($request->filled('captcha_site_key')) {
            Setting::set('captcha_site_key', $validated['captcha_site_key'], 'captcha');
        }
        if ($request->filled('captcha_secret_key')) {
            Setting::set('captcha_secret_key', $validated['captcha_secret_key'], 'captcha');
        }
        if ($request->filled('captcha_v3_threshold')) {
            Setting::set('captcha_v3_threshold', $validated['captcha_v3_threshold'], 'captcha');
        }

        // ── Timezone & Localization (PRD-ADDNEW 5.5) ──────────────────────────
        Setting::set('auto_detect_timezone', $request->has('auto_detect_timezone') ? '1' : '0', 'localization');
        if ($request->filled('site_timezone')) {
            Setting::set('site_timezone', $validated['site_timezone'], 'localization');
        }
        if ($request->filled('date_format')) {
            Setting::set('date_format', $validated['date_format'], 'localization');
        }
        if ($request->filled('time_format')) {
            Setting::set('time_format', $validated['time_format'], 'localization');
        }

        return back()->with('success', 'General settings and brand identity saved.');
    }
}
